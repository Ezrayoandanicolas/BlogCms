import base64
import os
import uuid
from io import BytesIO

import requests
from PIL import Image

from config import Config


DEFAULT_NEGATIVE_PROMPT = (
    "(worst quality, low quality:1.4), ugly, deformed, blurry, bad anatomy, "
    "bad hands, missing fingers, extra digits, fewer digits, extra limbs, "
    "extra arms, extra legs, malformed limbs, fused fingers, too many fingers, "
    "long neck, mutated hands, poorly drawn hands, poorly drawn face, mutation, "
    "deformed, bad proportions, gross proportions, cloned face, disfigured, "
    "unrealistic, (watermark:1.5), (text:1.3), (signature:1.3), (logo:1.3), "
    "(artist name:1.3), (username:1.3), (title:1.3), (date:1.3)"
)


def _generate_sd(prompt: str, output_dir: str) -> str | None:
    negative_prompt = Config.SD_NEGATIVE_PROMPT or DEFAULT_NEGATIVE_PROMPT

    payload = {
        "prompt": prompt,
        "negative_prompt": negative_prompt,
        "steps": Config.SD_STEPS,
        "sampler_name": Config.SD_SAMPLER,
        "sampler_index": Config.SD_SAMPLER,
        "width": Config.SD_WIDTH,
        "height": Config.SD_HEIGHT,
        "cfg_scale": Config.SD_CFG_SCALE,
        "seed": -1,
        "batch_size": 1,
        "n_iter": 1,
        "restore_faces": True,
        "enable_hr": Config.SD_HIRES_ENABLE,
        "hr_scale": Config.SD_HIRES_SCALE,
        "hr_upscaler": Config.SD_HIRES_UPSCALER,
        "hr_second_pass_steps": Config.SD_HIRES_STEPS,
        "denoising_strength": Config.SD_HIRES_DENOISING,
        "override_settings": {
            "sd_model_checkpoint": Config.SD_MODEL,
        },
    }

    try:
        res = requests.post(
            f"{Config.SD_WEBUI_URL}/sdapi/v1/txt2img",
            json=payload,
            timeout=180,
        )
        res.raise_for_status()
        image_data = res.json()["images"][0]

        image = Image.open(BytesIO(base64.b64decode(image_data)))
        image = image.convert("RGB")

        output_path = os.path.join(output_dir, f"{uuid.uuid4().hex}.jpg")
        image.save(output_path, format="JPEG", quality=85, optimize=True)

        if os.path.getsize(output_path) > 2000:
            return output_path

        print("   SD: file terlalu kecil")
        return None

    except requests.exceptions.ConnectionError:
        print("   SD WebUI tidak merespon")
        return None
    except Exception as e:
        print(f"   SD error: {e}")
        return None


def _generate_openrouter(prompt: str, output_dir: str) -> str | None:
    if not Config.OPENROUTER_API_KEY:
        return None

    from openrouter_client import OpenRouterClient

    client = OpenRouterClient(
        Config.OPENROUTER_API_KEY,
        Config.OPENROUTER_MODEL,
        Config.OPENROUTER_IMAGE_MODEL,
    )

    print(f"   Generate image via OpenRouter ({Config.OPENROUTER_IMAGE_MODEL})...")
    return client.generate_image(prompt, output_dir)


def generate_image_from_prompt(prompt: str, output_dir: str = "output") -> str | None:
    os.makedirs(output_dir, exist_ok=True)
    result = None

    result = _generate_sd(prompt, output_dir)
    if result:
        print(f"   SD WebUI: gambar siap")
        return result

    print("   SD gagal, fallback ke OpenRouter...")
    result = _generate_openrouter(prompt, output_dir)
    if result:
        print(f"   OpenRouter: gambar siap")
        return result

    print("   Semua metode image generation gagal")
    return None
