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

            $table->enum('exercise_family', [
                'ab_rollout',
                'bicep_curl_standard',
                'calf_raise',
                'chest_fly',
                'core_misc',
                'crunch_variation',
                'decline_bench_press',
                'dip_variation',
                'flat_bench_press',
                'forearm_isolation',
                'front_lateral_raise',
                'hammer_curl',
                'hip_hinge_hamstring',
                'horizontal_row',
                'incline_bench_press',
                'leg_curl',
                'leg_extension',
                'leg_machine_misc',
                'leg_press',
                'leg_raise_core',
                'lunge_variation',
                'machine_chest_press',
                'other_misc',
                'plank_variation',
                'preacher_curl',
                'pullover_variation',
                'pushup_variation',
                'rear_delt_variation',
                'recovery_abdominals',
                'recovery_abductors',
                'recovery_adductors',
                'recovery_biceps',
                'recovery_calves',
                'recovery_chest',
                'recovery_forearms',
                'recovery_glutes',
                'recovery_hamstrings',
                'recovery_lats',
                'recovery_lower_back',
                'recovery_middle_back',
                'recovery_neck',
                'recovery_quadriceps',
                'recovery_shoulders',
                'recovery_triceps',
                'shrug_variation',
                'side_lateral_raise',
                'situp_variation',
                'squat_variation',
                'tricep_extension',
                'tricep_pushdown',
                'upright_row',
                'vertical_overhead_press',
                'vertical_pull_bodyweight',
                'vertical_pull_machine',
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
