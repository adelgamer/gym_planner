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
        Schema::create('exercie_muscles', function (Blueprint $table) {
            $table->id();

            $table->string('exercie_id');
            $table->foreign('exercie_id')->references('id')->on('exercies')->cascadeOnDelete();

            $table->string('muscle_id');
            $table->foreign('muscle_id')->references('id')->on('muscles')->cascadeOnDelete();

            $table->enum('type', ['primary', 'secondary']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercie_muscles');
    }
};
