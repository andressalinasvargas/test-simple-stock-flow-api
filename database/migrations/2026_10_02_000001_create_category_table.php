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
        Schema::create('category', function (Blueprint $table) {
            $table->char('id', 36)->collation('ascii_bin')->primary();
            $table->string('name', 120)->unique('uq_category_name');
        });

        DB::statement('ALTER TABLE category ADD CONSTRAINT ck_category_name_not_blank CHECK (CHAR_LENGTH(TRIM(name)) > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('category');
    }
};

