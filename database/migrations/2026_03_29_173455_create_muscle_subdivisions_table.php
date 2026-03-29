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
        Schema::create('muscle_subdivisions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 256);
            $table->string('muscle_id');

            $table->foreign("muscle_id")->references("id")->on("muscles");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muscle_subdivisions');
    }
};
