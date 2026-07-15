<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso2', 2)->nullable();
            $table->string('iso3', 3)->unique();
            $table->string('capital')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->bigInteger('population')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 11, 6)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_symbol', 10)->nullable();
            $table->string('flag_emoji', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
