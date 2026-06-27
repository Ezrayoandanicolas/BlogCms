#!/usr/bin/env python3
import argparse
import json
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
    parser.add_argument("--from", dest="from_date", type=str, default="", help="Tanggal mulai schedule (format: YYYY-MM-DD)")
    parser.add_argument("--daily", action="store_true", help="Generate 1 artikel per domain hari ini")
    parser.add_argument("--retry", action="store_true", help="Proses ulang artikel yang gagal di retry_queue.json")
    parser.add_argument("--add-domain", nargs="+", help="Tambah domain baru (misal: --add-domain blog.example.com --port 7999)")
    parser.add_argument("--port", type=int, default=0, help="Port lokal untuk Cloudflare tunnel (contoh: 7999)")

    args = parser.parse_args()

    gen = ArticleGenerator(topic=args.topic)

    if args.add_domain:
        gen.add_domains(args.add_domain, port=args.port)
        return

    if args.retry:
        queue_file = os.path.join(os.path.dirname(__file__), "retry_queue.json")
        if not os.path.exists(queue_file):
            print("Tidak ada artikel dalam antrian retry.")
            return
        with open(queue_file) as f:
            queue = json.load(f)
        if not queue:
            print("Antrian retry kosong.")
            return
        print(f"Memproses {len(queue)} artikel dalam antrian retry...")
        gen._process_retry_queue()
        remaining = gen._load_retry_queue()
        if remaining:
            print(f"Masih {len(remaining)} artikel gagal, akan dicoba lagi nanti.")
        else:
            print("Semua artikel berhasil dipublikasikan!")
        return

    if args.daily:
        gen.run_daily()
        return

    if args.schedule:
        gen.run_schedule(days=args.schedule, start_date=args.from_date or None)
        return

    if args.topics:
        topics = gen.generate_topics()
        print(f"\nIde artikel untuk niche '{gen.topic}':")
        for i, t in enumerate(topics, 1):
            print(f"  {i}. {t}")
        return

    if args.count > 1:
        gen.run_batch(count=args.count, topic=args.topic)
    else:
        gen.generate_article(topic=args.topic, image_prompt_override=args.image_prompt)


if __name__ == "__main__":
    main()
