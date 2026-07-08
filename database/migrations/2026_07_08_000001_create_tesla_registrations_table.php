<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tesla_registrations', function (Blueprint $table) {
            $table->id();
            $table->date('registration_date');
            $table->string('model');
            $table->string('color');
            $table->unsignedInteger('count');
            $table->timestamps();

            $table->unique(['registration_date', 'model', 'color']);
            $table->index('registration_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tesla_registrations');
    }
};
