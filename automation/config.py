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

    OPENROUTER_API_KEY = os.getenv("OPENROUTER_API_KEY", "")
    OPENROUTER_MODEL = os.getenv("OPENROUTER_MODEL", "google/gemini-2.0-flash-001")
    OPENROUTER_IMAGE_MODEL = os.getenv("OPENROUTER_IMAGE_MODEL", "black-forest-labs/flux-schnell")
    OPENROUTER_FALLBACK_MODELS = list(filter(None, os.getenv("OPENROUTER_FALLBACK_MODELS", "google/gemma-3-27b-it:free,qwen/qwen3-8b:free").split(",")))

    SD_WEBUI_URL = os.getenv("SD_WEBUI_URL", "http://127.0.0.1:7860")
    SD_MODEL = os.getenv("SD_MODEL", "realisticVisionV51_v51VAE.safetensors")
    SD_STEPS = int(os.getenv("SD_STEPS", "25"))
    SD_WIDTH = int(os.getenv("SD_WIDTH", "640"))
    SD_HEIGHT = int(os.getenv("SD_HEIGHT", "384"))
    SD_CFG_SCALE = float(os.getenv("SD_CFG_SCALE", "5"))
    SD_SAMPLER = os.getenv("SD_SAMPLER", "DPM++ 2M Karras")
    SD_NEGATIVE_PROMPT = os.getenv("SD_NEGATIVE_PROMPT", "")
    SD_HIRES_ENABLE = os.getenv("SD_HIRES_ENABLE", "true").lower() == "true"
    SD_HIRES_SCALE = float(os.getenv("SD_HIRES_SCALE", "2"))
    SD_HIRES_UPSCALER = os.getenv("SD_HIRES_UPSCALER", "Latent")
    SD_HIRES_STEPS = int(os.getenv("SD_HIRES_STEPS", "15"))
    SD_HIRES_DENOISING = float(os.getenv("SD_HIRES_DENOISING", "0.4"))

    CLOUDFLARE_EMAIL = os.getenv("CLOUDFLARE_EMAIL", "")
    CLOUDFLARE_API_KEY = os.getenv("CLOUDFLARE_API_KEY", "")
    CLOUDFLARE_TUNNEL_ID = os.getenv("CLOUDFLARE_TUNNEL_ID", "")
