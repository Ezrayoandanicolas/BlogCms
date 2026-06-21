import base64
import os
import uuid

import requests
from PIL import Image
from io import BytesIO

from config import Config


def generate_image_from_prompt(prompt: str, output_dir: str = "output") -> str | None:
    os.makedirs(output_dir, exist_ok=True)
    output_path = os.path.join(output_dir, f"{uuid.uuid4().hex}.png")

    payload = {
        "prompt": prompt,
        "negative_prompt": "",
        "steps": Config.SD_STEPS,
        "sampler_name": "DPM++ 2M",
        "sampler_index": "DPM++ 2M",
        "width": Config.SD_WIDTH,
        "height": Config.SD_HEIGHT,
        "cfg_scale": 7,
        "seed": -1,
        "batch_size": 1,
        "n_iter": 1,
        "restore_faces": False,
        "enable_hr": False,
        "override_settings": {
            "sd_model_checkpoint": Config.SD_MODEL,
        },
    }

    try:
        res = requests.post(
            f"{Config.SD_WEBUI_URL}/sdapi/v1/txt2img",
            json=payload,
            timeout=120,
        )
        res.raise_for_status()
        image_data = res.json()["images"][0]

        image = Image.open(BytesIO(base64.b64decode(image_data)))
        image = image.convert("RGB")

        with open(output_path, "wb") as f:
            image.save(f, format="PNG", optimize=True)
            f.flush()
            os.fsync(f.fileno())

        if os.path.exists(output_path) and os.path.getsize(output_path) > 1000:
            return output_path
        else:
            print("❌ File gagal disimpan atau terlalu kecil.")
            return None

    except Exception as e:
        print(f"❌ Error saat generate gambar: {e}")
        return None
