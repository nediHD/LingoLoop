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
from models.youtube import *
from models.ai_title_generation import *

x = YOUTUBE()
y = AI_VOCABULARY_GENERATION()
#y = x.get_watched_video_ids(49)
#k,z = x.get_tittles(49)
#o = x.get_tittles(49)
#k = y.get_target_language(1)
#_ = y.getting_data_from_ab(1)
#o = y.generate_youtube_titles(_,k)
o = x.get_transcript_en("NG8kdn1BacA")
print(o)