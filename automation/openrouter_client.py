import base64
import json
import os
import time
import uuid
from io import BytesIO

import requests
from PIL import Image


class OpenRouterClient:
    def __init__(self, api_key: str, model: str, image_model: str = ""):
        self.base_url = "https://openrouter.ai/api/v1/chat/completions"
        self.api_key = api_key
        self.model = model
        self.image_model = image_model or "black-forest-labs/flux-schnell"
        self.timeout = 120

    def _headers(self) -> dict:
        return {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json",
        }

    def chat(self, messages: list[dict], system: str = "", temperature: float = 0.7) -> str:
        full_messages = []
        if system:
            full_messages.append({"role": "system", "content": system})
        full_messages.extend(messages)

        for attempt in range(3):
            try:
                resp = requests.post(
                    self.base_url,
                    headers=self._headers(),
                    json={
                        "model": self.model,
                        "messages": full_messages,
                        "temperature": temperature,
                    },
                    timeout=self.timeout,
                )
                resp.raise_for_status()
                return resp.json()["choices"][0]["message"]["content"]
            except (requests.RequestException, json.JSONDecodeError, KeyError) as e:
                if attempt == 2:
                    raise
                wait = (attempt + 1) * 5
                print(f"   OpenRouter chat error: {e}. Retry {wait}s...")
                time.sleep(wait)

    def generate_json(self, messages: list[dict], system: str = "", temperature: float = 0.3) -> dict:
        full_messages = []
        if system:
            full_messages.append({"role": "system", "content": system})
        full_messages.extend(messages)

        for attempt in range(3):
            try:
                resp = requests.post(
                    self.base_url,
                    headers=self._headers(),
                    json={
                        "model": self.model,
                        "messages": full_messages,
                        "temperature": temperature,
                        "response_format": {"type": "json_object"},
                    },
                    timeout=self.timeout,
                )
                resp.raise_for_status()
                content = resp.json()["choices"][0]["message"]["content"]
                return json.loads(content)
            except (json.JSONDecodeError, requests.RequestException, KeyError) as e:
                if attempt == 2:
                    break
                wait = (attempt + 1) * 5
                print(f"   OpenRouter JSON error: {e}. Retry {wait}s...")
                time.sleep(wait)

        for attempt in range(3):
            try:
                resp = requests.post(
                    self.base_url,
                    headers=self._headers(),
                    json={
                        "model": self.model,
                        "messages": full_messages,
                        "temperature": temperature,
                    },
                    timeout=self.timeout,
                )
                resp.raise_for_status()
                content = resp.json()["choices"][0]["message"]["content"]
                cleaned = content.strip()
                if cleaned.startswith("```"):
                    cleaned = cleaned.split("\n", 1)[-1]
                    cleaned = cleaned.rsplit("```", 1)[0]
                    cleaned = cleaned.strip()
                return json.loads(cleaned)
            except (json.JSONDecodeError, requests.RequestException, KeyError) as e:
                if attempt == 2:
                    break
                wait = (attempt + 1) * 10
                print(f"   OpenRouter fallback error: {e}. Retry {wait}s...")
                time.sleep(wait)

        return {}

    def generate_image(self, prompt: str, output_dir: str = "output") -> str | None:
        import re

        os.makedirs(output_dir, exist_ok=True)
        output_path = os.path.join(output_dir, f"{uuid.uuid4().hex}.jpg")

        for attempt in range(3):
            try:
                resp = requests.post(
                    self.base_url,
                    headers=self._headers(),
                    json={
                        "model": self.image_model,
                        "messages": [
                            {"role": "user", "content": f"Generate an image of: {prompt}"}
                        ],
                        "temperature": 1,
                    },
                    timeout=180,
                )
                resp.raise_for_status()
                data = resp.json()
                msg = data.get("choices", [{}])[0].get("message", {})

                image_data = None

                images = msg.get("images")
                if images and isinstance(images, list):
                    for img in images:
                        url = (img.get("image_url") or {}).get("url", "")
                        if url.startswith("data:image"):
                            image_data = url.split(",", 1)[1] if "," in url else url
                            break

                content = msg.get("content", "")
                if not image_data and content:
                    base64_matches = re.findall(r'data:image/\w+;base64,([a-zA-Z0-9+/=]+)', content)
                    if base64_matches:
                        image_data = base64_matches[0]
                    elif len(content) > 100:
                        try:
                            test_decode = base64.b64decode(content)
                            if len(test_decode) > 100:
                                image_data = content
                        except Exception:
                            pass

                if not image_data:
                    data_field = data.get("data")
                    if data_field and isinstance(data_field, list) and len(data_field) > 0:
                        img_entry = data_field[0]
                        if "b64_json" in img_entry:
                            image_data = img_entry["b64_json"]
                        elif "url" in img_entry:
                            img_resp = requests.get(img_entry["url"], timeout=60)
                            img_resp.raise_for_status()
                            with open(output_path, "wb") as f:
                                f.write(img_resp.content)
                            if os.path.getsize(output_path) > 2000:
                                return output_path
                        elif "content" in img_entry:
                            image_data = img_entry["content"]

                if image_data:
                    try:
                        raw = base64.b64decode(image_data)
                        img = Image.open(BytesIO(raw))

                        max_size = 1024
                        if img.width > max_size or img.height > max_size:
                            ratio = min(max_size / img.width, max_size / img.height)
                            img = img.resize((int(img.width * ratio), int(img.height * ratio)), Image.LANCZOS)

                        img = img.convert("RGB")
                        with open(output_path, "wb") as f:
                            img.save(f, format="JPEG", quality=85, optimize=True)

                        if os.path.getsize(output_path) > 2000:
                            return output_path
                    except Exception as e:
                        print(f"   Failed to decode image data: {e}")

                print(f"   OpenRouter image: no recognizable image data in response")
                if attempt < 2:
                    print(f"   Retry {attempt+2}/3...")
                    time.sleep(3)
                    continue
                return None

            except Exception as e:
                if attempt == 2:
                    print(f"   OpenRouter image error: {e}")
                    return None
                wait = (attempt + 1) * 5
                print(f"   OpenRouter image error: {e}. Retry {wait}s...")
                time.sleep(wait)

        return None
