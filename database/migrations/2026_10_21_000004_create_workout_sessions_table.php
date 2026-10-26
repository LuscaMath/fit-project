<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACTIVE_INDEX = 'workout_sessions_one_in_progress_per_user';

    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_routine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('routine_name_snapshot');
            $table->string('primary_modality_snapshot', 20);
            $table->string('status', 20);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'started_at']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX '.self::ACTIVE_INDEX." ON workout_sessions (user_id) WHERE status = 'in_progress'");
        }

        if ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX '.self::ACTIVE_INDEX." ON workout_sessions (user_id) WHERE status = 'in_progress'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['sqlite', 'pgsql'], true)) {
            DB::statement('DROP INDEX IF EXISTS '.self::ACTIVE_INDEX);
        }

        Schema::dropIfExists('workout_sessions');
    }
};
