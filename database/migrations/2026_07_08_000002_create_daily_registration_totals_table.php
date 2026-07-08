<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_registration_totals', function (Blueprint $table) {
            $table->id();
            $table->date('registration_date')->unique();
            $table->unsignedInteger('total_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_registration_totals');
    }
};
