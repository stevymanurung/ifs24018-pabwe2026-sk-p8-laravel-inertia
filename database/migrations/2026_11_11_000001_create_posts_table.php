<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function ($table): void {
            $table->string('category')->default('Umum')->change();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function ($table): void {
            $table->string('category')->default(null)->change();
        });
    }
};
