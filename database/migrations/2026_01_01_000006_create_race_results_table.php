<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grand_prix_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position')->nullable();
            $table->decimal('points', 4, 1)->default(0);
            $table->boolean('fastest_lap')->default(false);
            $table->boolean('pole_position')->default(false);
            $table->boolean('dnf')->default(false);       // did not finish
            $table->string('total_time')->nullable();     // e.g. "1:32:12.777"
            $table->unsignedSmallInteger('laps_completed')->nullable();
            $table->timestamps();

            // A driver can only have one result per GP
            $table->unique(['grand_prix_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
