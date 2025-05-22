import os
import json
from groq import Groq
import sys
sys.path.append("C:/xampp/htdocs/LingoLoop")
from models.Database import Database  # Prilagodi ako je putanja drugačija

class AI_VOCABULARY_GENERATION:
    def __init__(self):
        self.__connection = Database.get_instance()

        config_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'config', 'keys.json'))
        with open(config_path, 'r', encoding='utf-8') as file:
            data = json.load(file)
            groq_key = data["Groq"]

        self.__client = Groq(api_key=groq_key)

    def getting_data_from_ab(self, user_id):
        """Vrati sve vokabular riječi korisnika."""
        query = "SELECT term FROM user_vocabulary WHERE user_id = %s"
        try:
            with self.__connection.cursor() as cursor:
                cursor.execute(query, (user_id,))
                result = cursor.fetchall()
                return [row[0] for row in result]
        except Exception as e:
            print(f"[Error in getting_data_from_ab] {e}")
            return []

    def get_target_language(self, user_id):
        """Vrati ciljani jezik korisnika."""
        query = "SELECT target_language FROM user_profiles WHERE user_id = %s"
        try:
            with self.__connection.cursor() as cursor:
                cursor.execute(query, (user_id,))
                result = cursor.fetchone()
                return result[0] if result else "en"
        except Exception as e:
            print(f"[Error in get_target_language] {e}")
            return "en"

    def generate_youtube_titles(self, vocab_list, target_language):
        """Generiše YouTube pretrage bazirane na vokabularu i jeziku."""
        prompt = f"""
    You are an AI that creates YouTube video titles to help language learners.

    Generate exactly 3 YouTube video titles based on these words:
    {vocab_list}

    Language: {target_language}
Each YouTube video titles must be **short**, containing **no more than 5 to 10 words**.

    Return a valid Python list of 3 strings.
    Example: ["...", "...", "..."]
    Do not include any explanation.
    """
        try:
            completion = self.__client.chat.completions.create(
                model="llama3-8b-8192",
                messages=[
                    {"role": "system", "content": "You are a helpful assistant for English learners."},
                    {"role": "user", "content": prompt}
                ],
                temperature=0.7,
                max_tokens=150,
                top_p=1
            )
            content = completion.choices[0].message.content.strip()
            return eval(content) if content.startswith("[") else []
        except Exception as e:
            print(f"[Error in generate_youtube_titles] {e}")
            return []
