import sys
import os
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..')))
from core.Database import *
from groq import Groq
import json
import ast

class AI_VOCABULARY_GENERATION:
    def __init__(self):
        self.__connection = Database.get_instance()
        self.__cursor = self.__connection.cursor()
        with open('config/keys.json', 'r') as file:
            data = json.load(file)
            groq = data["Groq"] 
        self.__client = Groq(api_key=groq)
    

    def getting_data_from_ab(self, id):
        query = "SELECT * FROM user_profiles WHERE user_id = %s"
        self.__cursor.execute(query, (id,))

        column_names = [desc[0] for desc in self.__cursor.description]
        rezultati = self.__cursor.fetchall()

        output = ""

        for red in rezultati:
            output += " Red:\n"
            for col_name, value in zip(column_names, red):
                output += f"{col_name}: {value}\n"
            output += "----------\n"

        return output
    
    def create_vocab(self, text):
        completion = self.__client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {
                    "role": "system",
                    "content": "You are a professional teacher fluent in French."
                },
                {
                    "role": "user",
                    "content": (
                                "Hier sind detaillierte Informationen über einen Benutzer:\n\n"
                                f"{text}\n\n"
                                "Bitte generiere eine Liste von **15 anspruchsvollen englischen Vokabeln oder idiomatischen Ausdrücken**, "
                                "die besonders gut zu diesem Benutzerprofil passen.\n"
                                "Beziehe dich auf seine Interessen, Ziele und seinen Lernstil, um relevante Ausdrücke zu wählen.\n\n"
                                "**Gib für jeden Begriff Folgendes an:**\n"
                                "1. Der englische Begriff\n"
                                "2. Die passende deutsche Übersetzung **(mit Artikel bei Substantiven)**\n"
                                "3. Einen kurzen Beispielsatz auf Englisch\n\n"
                                "Format:\n"
                                "[\n"
                                "  ('term1', 'Übersetzung1', 'Beispielsatz1'),\n"
                                "  ('term2', 'Übersetzung2', 'Beispielsatz2'),\n"
                                "  ...\n"
                                "]\n\n"
                                "Gib **nur** die formatierte Python-kompatible Liste zurück – ohne zusätzliche Kommentare oder Erklärungen."
                            )
                }
            ],
            temperature=1,
            max_tokens=1024,
            top_p=1,
            stream=True,
            stop=None,
        )

        response_text = "".join(chunk.choices[0].delta.content or "" for chunk in completion)

        try:
            word_list = ast.literal_eval(response_text.strip())
            return word_list
        except Exception as e:
            print("Parsing error:", e)
            print("Raw response:", response_text)
            return []




x = AI_VOCABULARY_GENERATION()
text = x.getting_data_from_ab(1)

print(x.create_vocab(text))
