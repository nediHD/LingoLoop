import sys
import os
import json
import ast
import time

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
        query = "SELECT * FROM user_profiles WHERE user_id = %s"
        self.__cursor.execute(query, (id,))
        column_names = [desc[0] for desc in self.__cursor.description]
        result = self.__cursor.fetchall()

        output = ""
        target_language = "EN"
        for row in result:
            for col_name, value in zip(column_names, row):
                output += f"{col_name}: {value}\n"
                if col_name == "target_language":
                    target_language = value.upper().strip()
        return output, target_language

    def create_vocab(self, user_id):
        profile_text, language = self.getting_data_from_ab(user_id)

        if language == "FR":
            lang_name = "French"
            lang_code = "französischen"
            article_note = "(mit Artikel wie *le* oder *la*)"
        elif language == "ES":
            lang_name = "Spanish"
            lang_code = "spanischen"
            article_note = "(mit Artikel wie *el* oder *la*)"
        else:
            lang_name = "English"
            lang_code = "englischen"
            article_note = "(mit Artikel bei Substantiven)"

        completion = self.__client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {"role": "system", "content": f"You are a professional teacher fluent in {lang_name}."},
                {"role": "user", "content": (
                    f"Hier sind detaillierte Informationen über einen Benutzer:\n\n"
                    f"{profile_text}\n\n"
                    f"Bitte generiere eine Liste von **15 anspruchsvollen {lang_code} Vokabeln oder idiomatischen Ausdrücken**, "
                    "die besonders gut zu diesem Benutzerprofil passen.\n"
                    "Beziehe dich auf seine Interessen, Ziele und seinen Lernstil, um relevante Ausdrücke zu wählen.\n\n"
                    "**Gib für jeden Begriff Folgendes an:**\n"
                    f"1. Der Begriff auf {lang_name}\n"
                    f"2. Die passende deutsche Übersetzung {article_note}\n"
                    f"3. Einen kurzen Beispielsatz auf {lang_name}\n\n"
                    "Format:\n"
                    "[\n"
                    "  ('term1', 'Übersetzung1'),\n"
                    "  ('term2', 'Übersetzung2'),\n"
                    "  ...\n"
                    "]\n\n"
                    "Gib **nur** die formatierte Python-kompatible Liste zurück – ohne zusätzliche Kommentare oder Erklärungen."
                )}
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
        except Exception:
            return []

# 🔥 CLI poziv iz PHP-a
if __name__ == "__main__":
    user_id = int(sys.argv[1])
    x = AI_VOCABULARY_GENERATION()
    vocab_list = x.create_vocab(user_id)
    print(json.dumps(vocab_list))  # Final output

