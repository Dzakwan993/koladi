<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extension UUID
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;');

        // 2. Custom Enum Types jika PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                DO $$ BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'conversation_scope') THEN
                        CREATE TYPE public.conversation_scope AS ENUM ('workspace', 'company');
                    END IF;
                END $$;
            ");

            DB::statement("
                DO $$ BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'payment_method_enum') THEN
                        CREATE TYPE public.payment_method_enum AS ENUM ('midtrans', 'manual');
                    END IF;
                END $$;
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS public.conversation_scope CASCADE;');
            DB::statement('DROP TYPE IF EXISTS public.payment_method_enum CASCADE;');
        }
    }
};
