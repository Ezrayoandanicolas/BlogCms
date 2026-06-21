#!/usr/bin/env python3
import argparse
import os
import sys
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), ".env"))

from generator import ArticleGenerator


def main():
    parser = argparse.ArgumentParser(description="Generate dan publish artikel blog via AI")
    parser.add_argument("--count", type=int, default=1, help="Jumlah artikel yang digenerate")
    parser.add_argument("--topic", type=str, default="", help="Override topik niche (default dari .env)")
    parser.add_argument("--topics", action="store_true", help="Tampilkan ide artikel rekomendasi")
    parser.add_argument("--image-prompt", type=str, default="", help="Override prompt untuk featured image")
    parser.add_argument("--schedule", type=int, default=0, help="Generate N artikel per domain dengan jadwal harian")
    parser.add_argument("--daily", action="store_true", help="Generate 1 artikel per domain hari ini")
    parser.add_argument("--add-domain", nargs="+", help="Tambah domain baru (misal: --add-domain localhost:8104 localhost:8105)")

    args = parser.parse_args()

    gen = ArticleGenerator(topic=args.topic)

    if args.add_domain:
        gen.add_domains(args.add_domain)
        return

    if args.daily:
        gen.run_daily()
        return

    if args.schedule:
        gen.run_schedule(days=args.schedule)
        return

    if args.topics:
        topics = gen.generate_topics()
        print(f"\n📋 Ide artikel untuk niche '{gen.topic}':")
        for i, t in enumerate(topics, 1):
            print(f"  {i}. {t}")
        return

    if args.count > 1:
        gen.run_batch(count=args.count, topic=args.topic)
    else:
        gen.generate_article(topic=args.topic, image_prompt_override=args.image_prompt)


if __name__ == "__main__":
    main()
