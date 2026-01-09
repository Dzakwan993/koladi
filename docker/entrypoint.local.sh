#!/bin/sh
set -e

echo "🧪 Koladi LOCAL booting..."

# 1️⃣ Generate APP_KEY kalau belum ada
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  echo "🔑 Generating APP_KEY"
  php artisan key:generate
fi

# 2️⃣ Wait DB
echo "⏳ Waiting for PostgreSQL..."
until pg_isready -h "$DB_HOST" -U "$DB_USERNAME" -q; do
  sleep 1
done
echo "✅ Database ready"

# 3️⃣ Check table count
TABLE_COUNT=$(PGPASSWORD="$DB_PASSWORD" psql \
  -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" \
  -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';" \
  2>/dev/null | tr -d ' ')

# 4️⃣ Import SQL jika kosong
if [ "$TABLE_COUNT" = "0" ]; then
  echo "📥 Empty DB — importing Koladi.sql"
  if [ -f "/var/www/Koladi.sql" ]; then
    PGPASSWORD="$DB_PASSWORD" psql \
      -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" \
      < /var/www/Koladi.sql
    echo "✅ SQL imported"
  else
    echo "⚠️ Koladi.sql not found — skipping"
  fi
else
  echo "ℹ️ DB already has tables — skip import"
fi

# 5️⃣ Optional: migrate kalau ada tambahan migration
# php artisan migrate || true

# 6️⃣ Storage
php artisan storage:link || true

# 7️⃣ CLEAR ONLY (NO CACHE)
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Laravel LOCAL ready"

exec "$@"
