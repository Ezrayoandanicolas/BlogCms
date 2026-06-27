import os
import uuid

from config import Config


def generate_image_from_prompt(prompt: str, output_dir: str = "output") -> str | None:
    os.makedirs(output_dir, exist_ok=True)
    output_path = os.path.join(output_dir, f"{uuid.uuid4().hex}.png")

    if not Config.OPENROUTER_API_KEY:
        print("  OPENROUTER_API_KEY tidak diset, skip image generation")
        return None

    from openrouter_client import OpenRouterClient

    client = OpenRouterClient(
        Config.OPENROUTER_API_KEY,
        Config.OPENROUTER_MODEL,
        Config.OPENROUTER_IMAGE_MODEL,
    )

    print(f"   Generate image via OpenRouter ({Config.OPENROUTER_IMAGE_MODEL})...")
    result = client.generate_image(prompt, output_dir)
    return result
