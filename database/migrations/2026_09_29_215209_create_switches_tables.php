<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('raw_key_switches', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('ready');
            $table->string('review_reason')->nullable();
            $table->foreignId('scraper_id')
                ->nullable()
                ->constrained('scrapers')
                ->nullOnDelete();
            $table->string('url')->unique();
            $table->string('raw_name')->nullable();
            $table->string('raw_price')->nullable();
            $table->string('raw_manufacturer')->nullable();
            $table->string('raw_cover')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('scraped_at');
            $table->timestamps();
        });

        Schema::create('key_switches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_switch_id')
                ->nullable()
                ->constrained('raw_switches')
                ->nullOnDelete();
            $table->string('name');
            $table->string('price');
            $table->string('manufacturer');
            $table->string('cover');
            $table->json('product_images')->nullable();
            $table->json('imported_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_key_switches');
        Schema::dropIfExists('key_switches');
    }
};
