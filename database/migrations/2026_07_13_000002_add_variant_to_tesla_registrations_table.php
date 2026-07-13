<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tesla_registrations', function (Blueprint $table) {
            $table->string('variant')->default('Onbekend')->after('color');

            $table->dropUnique(['registration_date', 'model', 'color']);
            $table->unique(['registration_date', 'model', 'color', 'variant']);
        });
    }

    public function down(): void
    {
        Schema::table('tesla_registrations', function (Blueprint $table) {
            $table->dropUnique(['registration_date', 'model', 'color', 'variant']);
            $table->unique(['registration_date', 'model', 'color']);

            $table->dropColumn('variant');
        });
    }
};
