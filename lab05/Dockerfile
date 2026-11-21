FROM php:8.3-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite && \
    rm -rf /var/lib/apt/lists/*

COPY site/ /var/www/html/
COPY sql/schema.sql /tmp/schema.sql
RUN mkdir -p /var/www/db && \
    sqlite3 /var/www/db/db.sqlite < /tmp/schema.sql && \
    chown -R www-data:www-data /var/www/db && \
    rm /tmp/schema.sql

ENV SQLITE_DB_PATH=/var/www/db/db.sqlite
EXPOSE 80
