<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('z_pilotos', function (Blueprint $table) {
            $table->string('driver_id')->nullable()->unique();
        });

        Schema::table('z_escuderias', function (Blueprint $table) {
            $table->string('constructor_id')->nullable()->unique();
        });

        Schema::table('z_gp', function (Blueprint $table) {
            $table->unsignedSmallInteger('ronda')->nullable();

            $table->unique(
                ['temporada', 'ronda'],
                'z_gp_temporada_ronda_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('z_gp', function (Blueprint $table) {
            $table->dropUnique('z_gp_temporada_ronda_unique');
            $table->dropColumn('ronda');
        });

        Schema::table('z_escuderias', function (Blueprint $table) {
            $table->dropUnique(['constructor_id']);
            $table->dropColumn('constructor_id');
        });

        Schema::table('z_pilotos', function (Blueprint $table) {
            $table->dropUnique(['driver_id']);
            $table->dropColumn('driver_id');
        });
    }
};