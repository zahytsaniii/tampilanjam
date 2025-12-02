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
        Schema::create('prayer_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->time('imsak')->nullable();
            $table->time('subuh')->nullable();
            $table->time('syuruq')->nullable();
            $table->time('dhuha')->nullable();
            $table->time('dzuhur')->nullable();
            $table->time('ashar')->nullable();
            $table->time('maghrib')->nullable();
            $table->time('isya')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_schedules');
    }
};
