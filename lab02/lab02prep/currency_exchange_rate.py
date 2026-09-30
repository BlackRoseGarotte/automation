import json
import logging
import os
import sys
from pathlib import Path

import requests

API_URL = "http://localhost:8080/"
API_KEY = os.getenv("API_KEY", "EXAMPLE_API_KEY")
ROOT = Path(__file__).resolve().parent

logging.basicConfig(
    filename=ROOT / "error.log",
    level=logging.ERROR,
    format="%(asctime)s - %(levelname)s - %(message)s",
)


def fail(message):
    print(message)
    logging.error(message)


def main():
    if len(sys.argv) != 4:
        fail("Usage: currency_exchange_rate.py FROM TO YYYY-MM-DD")
        return

    from_currency, to_currency, date = sys.argv[1:]

    try:
        response = requests.post(
            API_URL,
            params={"from": from_currency, "to": to_currency, "date": date},
            data={"key": API_KEY},
            timeout=10,
        )
        response.raise_for_status()
        result = response.json()

        if result.get("error"):
            raise ValueError(result["error"])

        data = result["data"]
        data_dir = ROOT / "data"
        data_dir.mkdir(exist_ok=True)
        file_path = data_dir / f"{data['from']}_{data['to']}_{data['date']}.json"
        file_path.write_text(
            json.dumps(result, indent=4, ensure_ascii=False),
            encoding="utf-8",
        )

        print(f"Rate: {data['rate']}")
        print(f"Saved to: {file_path}")
    except Exception as error:
        fail(f"Error: {error}")


if __name__ == "__main__":
    main()