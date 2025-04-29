# ai_translation.py
import sys
import json
from groq import Groq
import os
sys.path.append("C:/xampp/htdocs/LingoLoop")
sys.stdout.reconfigure(encoding='utf-8')

config_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'config', 'keys.json'))
with open(config_path, 'r') as file:
    data = json.load(file)
    groq_key = data["Groq"]
client = Groq(api_key=groq_key)

def translate_text(text):
    prompt = f"""
Translate the following English word or phrase to German.

Only return the result as a tuple in Python format: (term, translation)
Do **not** include any explanation, commentary, formatting or extra text.

Text:
{text}
"""

    completion = client.chat.completions.create(
        model="llama-3.3-70b-versatile",
        messages=[
            {"role": "system", "content": "You are a professional translator fluent in English and German."},
            {"role": "user", "content": prompt}
        ],
        temperature=0.5,
        max_tokens=200
    )

    translated = completion.choices[0].message.content.strip()
    return translated

if __name__ == "__main__":
    input_text = sys.argv[1]
    result = translate_text(input_text)

    if result.startswith("(") and result.endswith(")"):
        print(result)
    else:
        print(f"(\"{input_text}\", \"[translation error]\")")
