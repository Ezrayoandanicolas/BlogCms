import json
import time

import requests


class OllamaClient:
    def __init__(self, base_url: str, model: str):
        self.base_url = base_url.rstrip("/")
        self.model = model
        self.timeout = 600

    def chat(self, messages: list[dict], system: str = "", temperature: float = 0.7) -> str:
        full_messages = []
        if system:
            full_messages.append({"role": "system", "content": system})
        full_messages.extend(messages)

        for attempt in range(3):
            try:
                resp = requests.post(
                    self.base_url,
                    json={
                        "model": self.model,
                        "messages": full_messages,
                        "stream": False,
                        "temperature": temperature,
                    },
                    timeout=self.timeout,
                )
                resp.raise_for_status()
                return resp.json()["message"]["content"]
            except (requests.RequestException, json.JSONDecodeError) as e:
                if attempt < 2:
                    wait = (attempt + 1) * 5
                    print(f"   ⚠️ Ollama chat error: {e}. Retry dalam {wait}s...")
                    time.sleep(wait)
                else:
                    raise

    def generate_json(self, messages: list[dict], system: str = "", temperature: float = 0.3) -> dict:
        full_messages = []
        if system:
            full_messages.append({"role": "system", "content": system})
        full_messages.extend(messages)

        payload = {
            "model": self.model,
            "messages": full_messages,
            "stream": False,
            "temperature": temperature,
        }

        for attempt in range(3):
            try:
                payload["format"] = "json"
                resp = requests.post(self.base_url, json=payload, timeout=self.timeout)
                resp.raise_for_status()
                content = resp.json()["message"]["content"]
                return json.loads(content)
            except (json.JSONDecodeError, requests.RequestException) as e:
                if attempt == 2:
                    raise
                wait = (attempt + 1) * 5
                print(f"   ⚠️ Ollama JSON error: {e}. Retry tanpa format json...")
                time.sleep(wait)

        # Fallback: coba tanpa format json
        for attempt in range(3):
            try:
                payload.pop("format", None)
                resp = requests.post(self.base_url, json=payload, timeout=self.timeout)
                resp.raise_for_status()
                content = resp.json()["message"]["content"]
                cleaned = content.strip()
                if cleaned.startswith("```"):
                    cleaned = cleaned.split("\n", 1)[-1]
                    cleaned = cleaned.rsplit("```", 1)[0]
                    cleaned = cleaned.strip()
                return json.loads(cleaned)
            except (json.JSONDecodeError, requests.RequestException) as e:
                if attempt == 2:
                    raise
                wait = (attempt + 1) * 10
                print(f"   ⚠️ Ollama fallback error: {e}. Retry dalam {wait}s...")
                time.sleep(wait)

        return {}
