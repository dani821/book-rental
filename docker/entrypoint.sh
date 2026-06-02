#!/bin/sh

set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "Waiting for the database at ${DB_HOST}:${DB_PORT}..."
until php -r '$h=getenv("DB_HOST")?:"db"; $p=(int)(getenv("DB_PORT")?:3306); exit(@fsockopen($h,$p)?0:1);'; do
  sleep 2
done
echo "Database is ready."

if [ ! -f .env ]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

php artisan migrate --force

STATE="$(php artisan tinker --execute="echo DB::table('users')->exists() ? 'seeded' : 'empty';" 2>/dev/null | grep -o -m1 'seeded\|empty' || true)"
if [ "$STATE" = "empty" ]; then
  echo "Seeding the database..."
  php artisan db:seed --force
fi

exec "$@"
