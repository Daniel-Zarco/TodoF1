<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circuits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->string('city')->nullable();
            $table->decimal('length_km', 5, 3)->nullable();
            $table->unsignedSmallInteger('lap_count')->nullable();
            $table->string('lap_record')->nullable();       // e.g. "1:19.119"
            $table->string('lap_record_driver')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circuits');
    }
};
