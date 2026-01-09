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

# 4️⃣ Import SQL ONLY if DB empty
if [ "$TABLE_COUNT" = "0" ]; then
  echo "📥 Database empty — importing Koladi.sql"
  if [ -f "/var/www/Koladi.sql" ]; then
    PGPASSWORD="$DB_PASSWORD" psql \
      -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" \
      < /var/www/Koladi.sql
    echo "✅ Koladi.sql imported"
  else
    echo "❌ Koladi.sql NOT FOUND"
    exit 1
  fi
else
  echo "✅ Database already initialized — skipping import"
fi

# 5️⃣ Run migrations ONLY if needed (optional)
# php artisan migrate --force || true

# 6️⃣ Storage + cache
php artisan storage:link || true
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Laravel production ready"

exec "$@"
