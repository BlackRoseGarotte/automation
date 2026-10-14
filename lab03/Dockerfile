FROM python:3.12-slim
WORKDIR /app
RUN apt-get update && apt-get install -y --no-install-recommends cron tzdata && rm -rf /var/lib/apt/lists/*
RUN pip install --no-cache-dir requests
COPY currency_exchange_rate.py /app/currency_exchange_rate.py
COPY cronjob /etc/cron.d/currency-cron
COPY entrypoint.sh /entrypoint.sh
RUN chmod 0644 /etc/cron.d/currency-cron && chmod +x /entrypoint.sh && mkdir -p /app/data
ENTRYPOINT ["/entrypoint.sh"]
