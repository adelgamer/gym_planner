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
        Schema::create('exercies', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 256);
            $table->text('instructions')->nullable();
            $table->enum('force', ['pull', 'push', 'static'])->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'expert'])->nullable();
            $table->enum('mechanic', ['compound', 'isolation'])->nullable();
            $table->integer('popularity')->nullable(); // 0 -> 10

            // Replaced category_id with a category enum
            $table->enum('category', [
                'strength',
                'stretching',
                'plyometrics',
                'strongman',
                'powerlifting',
                'cardio',
                'olympic weightlifting'
            ])->nullable();

            $table->foreignId('equipment_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercies');
    }
};
