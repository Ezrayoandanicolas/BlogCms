import os
from pathlib import Path
from dotenv import load_dotenv

load_dotenv(Path(__file__).parent / ".env")


class Config:
    BLOGCMS_URL = os.getenv("BLOGCMS_URL", "http://localhost:8101")
    BLOGCMS_EMAIL = os.getenv("BLOGCMS_EMAIL", "admin@blogcms.test")
    BLOGCMS_PASSWORD = os.getenv("BLOGCMS_PASSWORD", "password")
    BLOGCMS_TOKEN = os.getenv("BLOGCMS_TOKEN", "")
    BLOGCMS_API_KEY = os.getenv("BLOGCMS_API_KEY", "")
    BLOGCMS_TOPIC = os.getenv("BLOGCMS_TOPIC", "")

    OLLAMA_URL = os.getenv("OLLAMA_URL", "http://localhost:11434/api/chat")
    OLLAMA_MODEL = os.getenv("OLLAMA_MODEL", "llama3.2")

    SD_WEBUI_URL = os.getenv("SD_WEBUI_URL", "http://127.0.0.1:7860")
    SD_MODEL = os.getenv("SD_MODEL", "mdjrny-v4.safetensors")
    SD_STEPS = int(os.getenv("SD_STEPS", "20"))
    SD_WIDTH = int(os.getenv("SD_WIDTH", "768"))
    SD_HEIGHT = int(os.getenv("SD_HEIGHT", "512"))
