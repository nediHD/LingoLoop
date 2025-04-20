
from groq import Groq
import sys
import os
import json
import ast

class AI_TRANSLATION:
    def __init__(self):
        config_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'config', 'keys.json'))
        with open(config_path, 'r') as file:

            data = json.load(file)
            groq = data["Groq"]
        self.__client = Groq(api_key=groq)

    def decode_unicode_escapes(self, text: str) -> str:
        return text.encode('utf-8').decode('unicode_escape')

    def translate(self, text):
        completion = self.__client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {
                    "role": "system",
                    "content": (
                        "Du bist eine professionelle Übersetzungsmaschine. "
                        "Du übersetzt präzise vom Englischen ins Deutsche – ohne zusätzliche Informationen oder Erklärungen."
                    )
                },
                {
                    "role": "user",
                    "content": (
                        "Übersetze den folgenden englischen Text ins Deutsche.\n\n"
                        "Wenn es sich um ein einzelnes Substantiv handelt, gib den bestimmten Artikel mit an "
                        "(z. B. „der Hund“, „die Katze“, „das Buch“).\n"
                        "Wenn es sich um einen Satz handelt, gib einfach die vollständige Übersetzung zurück – "
                        "ohne Beispiele oder Kommentare.\n\n"
                        f"Text: {text}"
                    )
                }
            ],
            temperature=0.2,
            max_tokens=100,
            top_p=1,
            stream=False
        )

        raw_text = completion.choices[0].message.content.strip()
        return self.decode_unicode_escapes(raw_text)



# 🔥 CLI poziv iz PHP-a

if __name__ == "__main__":
    text_to_translate = sys.argv[1] if len(sys.argv) > 1 else "It will be a logistical nightmare for Ukraine’s forces to suddenly, immediately stop fighting at Putin’s behest."
    translator = AI_TRANSLATION()
    result = translator.translate(text_to_translate)
    print(json.dumps(result, ensure_ascii=False))  # Final output with proper UTF-8

