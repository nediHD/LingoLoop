import sys
import os
import json
import ast
import time  # ⏰ dodaj import

# Podesi putanju do core/
sys.path.append("C:/xampp/htdocs/LingoLoop")
from models.Database import *
from groq import Groq

class AI_VOCABULARY_GENERATION:
    def __init__(self):
        self.__connection = Database.get_instance()
        self.__cursor = self.__connection.cursor()
        config_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'config', 'keys.json'))
        with open(config_path, 'r') as file:

            data = json.load(file)
            groq = data["Groq"]
        self.__client = Groq(api_key=groq)
    
    def getting_data_from_ab(self, id):
        query = "SELECT term FROM user_vocabulary WHERE user_id = %s AND next_review_date >= CURDATE() "
        self.__cursor.execute(query, (id,))
        column_names = [desc[0] for desc in self.__cursor.description]
        rezultati = self.__cursor.fetchall()

        output = ""
        for red in rezultati:
            for col_name, value in zip(column_names, red):
                output += f"{col_name}: {value}\n"
        return output

    def generate_youtube_titles(self, vocab_list):
        prompt = f"""
        You are a creative English teacher and YouTube content creator.

Here is a list of English vocabulary or idiomatic expressions that a language learner wants to study:

{vocab_list}

Based on this list, generate exactly **3 YouTube video titles** in **English**.

Each title should:
- Be short (just a few words, not full sentences)
- Be catchy and creative
- Be directly related to the vocabulary
- Sound like a real YouTube video someone would want to click
- Be helpful for someone learning English

⚠️ Return the result as a valid Python list of 3 strings. No explanations. No bullet points. Just something like:
["Title one", "Title two", "Title three"]
"""
        completion = self.__client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {"role": "system", "content": "You are a creative and engaging English language YouTube content creator."},
                {"role": "user", "content": prompt}
            ],
            temperature=0.9,
            max_tokens=100,
            top_p=1
        )
        response = completion.choices[0].message.content.strip()

        try:
            # Parse the result into a real Python list
            titles = ast.literal_eval(response)
            return titles
        except Exception as e:
            # fallback in case parsing fails
            return []


# 🔥 CLI poziv iz PHP-a
if __name__ == "__main__":
    x = AI_VOCABULARY_GENERATION()
    z =x.getting_data_from_ab(1)
    k = x.generate_youtube_titles(z)
    print(z)
    print(k)



    #print(json.dumps(result))# Final output


        