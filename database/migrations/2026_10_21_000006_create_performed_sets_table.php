<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performed_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('set_number');
            $table->unsignedSmallInteger('repetitions')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('load_kg', 8, 2)->unsigned()->nullable();
            $table->uuid('client_request_id')->unique();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['session_exercise_id', 'set_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performed_sets');
    }
};
