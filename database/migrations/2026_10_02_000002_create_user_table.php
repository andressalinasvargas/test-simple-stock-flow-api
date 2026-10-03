<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->char('id', 36)->collation('ascii_bin')->primary();
            $table->string('username', 120)->unique('uq_user_username');
            $table->string('password_hash', 512)->collation('ascii_bin');
            $table->string('role', 40)->collation('ascii_bin');
        });

        DB::statement("ALTER TABLE `user` ADD CONSTRAINT ck_user_username_normalized CHECK (CHAR_LENGTH(username) > 0 AND CAST(username AS BINARY) = CAST(LOWER(TRIM(username)) AS BINARY))");
        DB::statement("ALTER TABLE `user` ADD CONSTRAINT ck_user_role_allowed CHECK (role IN ('admin','seller'))");
        DB::statement("ALTER TABLE `user` ADD CONSTRAINT ck_user_password_hash_not_blank CHECK (CHAR_LENGTH(password_hash) > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};

