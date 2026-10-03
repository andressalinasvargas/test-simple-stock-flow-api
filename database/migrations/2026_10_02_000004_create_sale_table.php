<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale', function (Blueprint $table) {
            $table->char('id', 36)->collation('ascii_bin')->primary();
            $table->dateTime('sold_at', 6);
            $table->string('sold_by_username', 120);
            $table->char('sold_by_user_id', 36)->collation('ascii_bin');

            $table->foreign('sold_by_user_id', 'fk_sale_sold_by_user_id')
                ->references('id')
                ->on('user')
                ->onDelete('restrict')
                ->onUpdate('no action');

            $table->index('sold_at', 'idx_sale_sold_at');
            $table->index('sold_by_user_id', 'idx_sale_sold_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale');
    }
};

