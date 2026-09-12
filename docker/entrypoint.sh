#!/bin/sh
set -e

echo "🚀 Koladi production booting..."

composer install --no-dev --optimize-autoloader --no-interaction

# 1️⃣ Generate key (safe)
php artisan key:generate --force || true

# 2️⃣ Wait DB ready
echo "⏳ Waiting for PostgreSQL..."
until pg_isready -h "$DB_HOST" -U "$DB_USERNAME" -q; do
  sleep 2
done
echo "✅ Database ready"

# 3️⃣ Check existing tables
TABLE_COUNT=$(PGPASSWORD="$DB_PASSWORD" psql \
  -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" \
  -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';" \
  2>/dev/null | tr -d ' ')

# 4️⃣ Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# 5️⃣ Seed initial data ONLY if DB was empty
if [ "$TABLE_COUNT" = "0" ]; then
  echo "🌱 Database empty — seeding initial data..."
  php artisan db:seed --force || true
else
  echo "✅ Database already initialized"
fi

# 6️⃣ Storage + cache
php artisan storage:link || true
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Laravel production ready"


exec "$@"
