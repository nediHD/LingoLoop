from googleapiclient.discovery import build
from datetime import timedelta
from ai_title_generation import *
import isodate
import sys
import os
import json
import ast
from youtube_transcript_api import YouTubeTranscriptApi
from youtube_transcript_api._errors import TranscriptsDisabled, NoTranscriptAvailable
from groq import Groq
import textwrap
sys.path.append("C:/xampp/htdocs/LingoLoop")
sys.stdout.reconfigure(encoding='utf-8')
sys.path.append("C:/xampp/htdocs/LingoLoop")
from models.Database import *


class YOUTUBE:
    def __init__(self):
        config_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'config', 'keys.json'))
        with open(config_path, 'r') as file:
            data = json.load(file)
            self.__API_key = data["Google"]
            groq_key = data["Groq"]

        self.__title = AI_VOCABULARY_GENERATION()
        self.__client = Groq(api_key=groq_key)
        self.__connection = Database.get_instance()



    def get_watched_video_ids(self, user_id):
        try:
            with self.__connection.cursor() as cursor:
                cursor.execute("SELECT video_id FROM watched_videos WHERE user_id = %s", (user_id,))
                result = cursor.fetchall()
                if result:
                    return [row[0] for row in result]
                else:
                    return []
        except Exception as e:
            print(f"[Error fetching watched videos] {e}")
            return False

    def get_tittles(self, user_id):
        user_data = self.__title.getting_data_from_ab(user_id)
        target_language = self.__title.get_target_language(user_id)
        titles = self.__title.generate_youtube_titles(user_data, target_language)
        return titles


    def search_youtube_videos(self, query, user_id, max_results=50, min_views=50000, return_limit=3):
        youtube = build("youtube", "v3", developerKey=self.__API_key)

        # Pretraga YouTube-a
        search_response = youtube.search().list(
            part="id",
            q=query,
            type="video",
            maxResults=max_results,
            videoDuration="medium"
        ).execute()

        video_ids = [item["id"]["videoId"] for item in search_response["items"]]

        # Dohvat dodatnih podataka o videima
        videos_response = youtube.videos().list(
            part="snippet,contentDetails,statistics",
            id=",".join(video_ids)
        ).execute()

        # Dohvati već gledane video ID-eve
        watched_ids = self.get_watched_video_ids(user_id)
        filtered_videos = []

        # Obrada svakog videa
        for item in videos_response["items"]:
            video_id = item["id"]

            if watched_ids:
                if video_id in watched_ids:
                    continue

            try:
                # Filtracija po trajanju i gledanosti (bez provjere transkripta)
                duration_iso = item["contentDetails"]["duration"]
                duration = isodate.parse_duration(duration_iso)
                view_count = int(item["statistics"]["viewCount"])

                if timedelta(minutes=8) <= duration <= timedelta(minutes=15) and view_count >= min_views:
                    video_data = {
                        "title": item["snippet"]["title"],
                        "url": f"https://www.youtube.com/watch?v={video_id}",
                        "duration": str(duration),
                        "views": view_count
                    }
                    filtered_videos.append(video_data)

                    if len(filtered_videos) >= return_limit:
                        break

            except Exception:
                continue

        return filtered_videos




    def get_transcript_en(self, video_url):
        try:
            video_id = video_url.split("v=")[-1]
            transcript = YouTubeTranscriptApi.get_transcript(video_id, languages=['fr'])
            text = " ".join([entry["text"] for entry in transcript])
            return text
        except (TranscriptsDisabled, NoTranscriptAvailable):
            return "[Transcript not available]"
        except Exception as e:
            return f"[Error: {str(e)}]"

    def find_top_videos(self, vocab_list, video_data_list, top_n=4):
        # Ukloni duplikate na osnovu URL-a
        unique_videos = {video["url"]: video for video in video_data_list}.values()

        for video in unique_videos:
            video["transcript"] = video["transcript"][:1000]

        prompt = f"""
You are an expert English tutor helping students find the best YouTube videos for studying vocabulary.

The student wants to learn these words and expressions:
{vocab_list}

Here is a list of YouTube videos with titles, URLs, and transcripts. From this list, select the **TOP {top_n}** videos that are most useful for learning these words.

Prefer videos where the vocabulary appears in the transcript, or the content is closely related.

Return a valid Python list of dictionaries like:
[
  {{"title": "...", "url": "..."}},
  ...
]
No explanations, no bullet points.
Videos:
{json.dumps(list(unique_videos), ensure_ascii=False, indent=2)}
"""
        completion = self.__client.chat.completions.create(
            model="llama3-8b-8192",
            messages=[
                {"role": "system", "content": "You are a helpful English language tutor and recommender."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.7,
            max_tokens=900,
            top_p=1
        )
        try:
            return ast.literal_eval(completion.choices[0].message.content.strip())
        except Exception:
            return []

    def generate_short_description(self, transcript):
        prompt = f"""
You are a helpful assistant. Summarize the following transcript into a **very short YouTube description** that is:

- Maximum 50 characters
- Simple and clear
- Describes what the video is about

Transcript:
\"\"\"
{transcript}
\"\"\"

Return only the description string, no bullet points, no extra formatting.
"""
        try:
            completion = self.__client.chat.completions.create(
                model="llama3-8b-8192",
                messages=[
                    {"role": "system", "content": "You are a concise YouTube video summarizer."},
                    {"role": "user", "content": prompt}
                ],
                temperature=0.7,
                max_tokens=50,
                top_p=1
            )
            return completion.choices[0].message.content.strip()
        except Exception:
            return "No description generated"

    def display_video_summary(self, video_url, transcript):
        youtube = build("youtube", "v3", developerKey=self.__API_key)
        video_id = video_url.split("v=")[-1]

        response = youtube.videos().list(
            part="snippet,contentDetails",
            id=video_id
        ).execute()

        if not response["items"]:
            return

        item = response["items"][0]
        title = item["snippet"]["title"]
        description = self.generate_short_description(transcript)
        duration = isodate.parse_duration(item["contentDetails"]["duration"])

        return [title,description,str(duration),f'https://www.youtube.com/watch?v={video_id}']
    def fetch_top_video_summaries(self, user_id, max_videos=10, top_n=4):
        queries, words = self.get_tittles(user_id)

        all_video_data = []
        seen_urls = set()

        for query in queries:
            results = self.search_youtube_videos(query, user_id=user_id)
            for video in results:
                if video["url"] not in seen_urls:
                    seen_urls.add(video["url"])
                    transcript = self.get_transcript_en(video["url"])
                    short_transcript = transcript[:1000]
                    all_video_data.append({
                        "title": video["title"],
                        "url": video["url"],
                        "transcript": short_transcript
                    })
                if len(all_video_data) >= max_videos:
                    break

        top_videos = self.find_top_videos(words, all_video_data, top_n=top_n)
        summaries = []

        for video in top_videos:
            match = next((v for v in all_video_data if v["url"] == video["url"]), None)
            if match:
                summary = self.display_video_summary(video["url"], match["transcript"])
                if summary:
                    summaries.append(summary)

        return summaries
    
  

    def convert_transcript_to_readable_text(self, video_url):
        import textwrap

        transcript = self.get_transcript_en(video_url)
        if not transcript or transcript.startswith("["):
            return "Transcript not available or could not be retrieved."

        # Chunk samo za token kontrolu, ne skraćivanje
        chunks = textwrap.wrap(transcript, width=3500)
        full_output = ""

        for i, chunk in enumerate(chunks):
            prompt = f"""
    Vous êtes un assistant utile. Voici une transcription brute d'une vidéo YouTube.
    Votre tâche consiste à la reformuler légèrement pour en faire un article clair, bien structuré et lisible, tout en **gardant 100 % du contenu original**.

    ⚠️ INSTRUCTIONS :
    - Ne changez, ne supprimez, ni ne résumez aucune information.
    - Ne traduisez pas.
    - N’inventez rien.
    - Respectez strictement l’ordre et le contenu.
    - Corrigez uniquement la grammaire, la ponctuation et la structure des phrases pour améliorer la lecture.

    Transcription – Partie {i+1} :
    \"\"\"{chunk}\"\"\"

    Réécrivez ce texte en article fluide avec des paragraphes. Retournez uniquement le texte réécrit.
    """

            try:
                completion = self.__client.chat.completions.create(
                    model="llama3-8b-8192",
                    messages=[
                        {"role": "system", "content": "Vous êtes un assistant qui améliore la lisibilité des transcriptions sans en modifier le contenu."},
                        {"role": "user", "content": prompt}
                    ],
                    temperature=0.3,
                    max_tokens=1800,
                    top_p=1
                )
                part_output = completion.choices[0].message.content.strip()
                full_output += part_output + "\n\n"
            except Exception as e:
                full_output += f"[Erreur lors du traitement de la partie {i+1} : {str(e)}]\n\n"

        return full_output.strip()

    





if __name__ == "__main__":
    yt = YOUTUBE()
    
    if len(sys.argv) == 2:
        # Samo jedan argument
        param = sys.argv[1]
        if param.startswith("http"):
            # Radi se o video URL-u
            result = yt.convert_transcript_to_readable_text(param)
            print(result)
        else:
            # Pretpostavljamo da je user_id
            user_id = int(param)
            result = yt.get_tittles(user_id=user_id)
            print(json.dumps(result, ensure_ascii=False, indent=2))
    
    elif len(sys.argv) == 3 and sys.argv[2] == "transcript":
        video_url = sys.argv[1]
        result = yt.convert_transcript_to_readable_text(video_url)
        print(result)
    
    else:
        print("[Error: Invalid arguments provided.]")

    
