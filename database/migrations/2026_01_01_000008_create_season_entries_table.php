<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // A driver can only be entered once per season per team (or simply once per season?)
            // A driver might change teams mid-season, but for now we keep it simple or allow multiple?
            // Actually, a driver CAN change teams mid-season (e.g. Albon/Gasly swap in 2019).
            // So unique should be: season_id, driver_id, team_id
            $table->unique(['season_id', 'driver_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_entries');
    }
};
