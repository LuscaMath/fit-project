<?php

namespace App\Actions;

use App\Enums\WorkoutSessionStatus;
use App\Models\User;
use App\Models\WorkoutRoutine;
use App\Models\WorkoutSession;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartWorkoutSession
{
    public function execute(User $user, WorkoutRoutine $routine): WorkoutSession
    {
        try {
            return DB::transaction(function () use ($user, $routine): WorkoutSession {
                $activeSession = WorkoutSession::query()
                    ->where('user_id', $user->id)
                    ->where('status', WorkoutSessionStatus::InProgress->value)
                    ->lockForUpdate()
                    ->first();

                if ($activeSession) {
                    return $activeSession;
                }

                $routine = WorkoutRoutine::query()
                    ->where('user_id', $user->id)
                    ->whereKey($routine->getKey())
                    ->with('routineExercises.exercise')
                    ->firstOrFail();

                if (! $routine->is_active) {
                    throw ValidationException::withMessages([
                        'routine' => 'Rotinas arquivadas não podem iniciar uma sessão.',
                    ]);
                }

                if ($routine->routineExercises->isEmpty()) {
                    throw ValidationException::withMessages([
                        'routine' => 'A rotina precisa ter pelo menos um exercício.',
                    ]);
                }

                $session = $user->workoutSessions()->create([
                    'workout_routine_id' => $routine->id,
                    'routine_name_snapshot' => $routine->name,
                    'primary_modality_snapshot' => $routine->primary_modality,
                    'status' => WorkoutSessionStatus::InProgress,
                    'started_at' => now(),
                ]);

                foreach ($routine->routineExercises as $routineExercise) {
                    $session->sessionExercises()->create([
                        'exercise_id' => $routineExercise->exercise_id,
                        'exercise_name_snapshot' => $routineExercise->exercise->name,
                        'measurement_type_snapshot' => $routineExercise->exercise->measurement_type,
                        'position' => $routineExercise->position,
                        'planned_sets' => $routineExercise->planned_sets,
                        'target_reps' => $routineExercise->target_reps,
                        'target_duration_seconds' => $routineExercise->target_duration_seconds,
                        'target_load_kg' => $routineExercise->target_load_kg,
                        'rest_seconds' => $routineExercise->rest_seconds,
                    ]);
                }

                return $session;
            });
        } catch (QueryException $exception) {
            $activeSession = WorkoutSession::query()
                ->where('user_id', $user->id)
                ->where('status', WorkoutSessionStatus::InProgress->value)
                ->first();

            if ($activeSession) {
                return $activeSession;
            }

            throw $exception;
        }
    }
}
