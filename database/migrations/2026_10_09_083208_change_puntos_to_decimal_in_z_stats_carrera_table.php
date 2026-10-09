<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('z_stats_carrera', function (Blueprint $table) {
            $table->decimal('puntos', 8, 2)
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('z_stats_carrera', function (Blueprint $table) {
            $table->integer('puntos')
                ->nullable()
                ->change();
        });
    }
};