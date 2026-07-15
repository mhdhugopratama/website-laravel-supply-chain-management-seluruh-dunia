<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique();
            $table->longText('articles');
            $table->decimal('positive_pct', 5, 2)->default(0);
            $table->decimal('neutral_pct', 5, 2)->default(0);
            $table->decimal('negative_pct', 5, 2)->default(0);
            $table->timestamp('cached_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_cache');
    }
};
