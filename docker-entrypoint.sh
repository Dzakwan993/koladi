#!/bin/bash
set -e

echo "🚀 Starting Koladi setup..."

# ✅ 1. Install dependencies jika belum ada
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# ✅ 2. Copy .env jika belum ada
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    if [ -f ".env.docker.example" ]; then
        cp .env.docker.example .env
    else
        cp .env.example .env
    fi
fi

# ✅ 3. Generate APP_KEY jika kosong
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# ✅ 4. Wait for database to be ready
echo "⏳ Waiting for database..."
until pg_isready -h db -U postgres -q; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "✅ Database is ready!"

# ✅ 5. Check if database is empty (need import)
TABLE_COUNT=$(PGPASSWORD=postgres psql -h db -U postgres -d koladi -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';" 2>/dev/null || echo "0")

if [ "$TABLE_COUNT" -eq "0" ]; then
    echo "📥 Database is empty, importing Koladi.sql..."
    if [ -f "Koladi.sql" ]; then
        PGPASSWORD=postgres psql -h db -U postgres -d koladi < Koladi.sql
        echo "✅ Database imported successfully!"
    else
        echo "⚠️  Koladi.sql not found, running migrations instead..."
        php artisan migrate --force
    fi
else
    echo "✅ Database already has tables, skipping import"
fi

# ✅ 6. Run seeder (idempotent)
echo "🌱 Running database seeder..."
php artisan db:seed --force

# ✅ 7. Create storage link
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# ✅ 8. Fix permissions
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "✅ Setup complete! Starting application..."

# ✅ 9. Run the main command
exec "$@"