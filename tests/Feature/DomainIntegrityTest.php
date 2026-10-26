<?php

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Enums\WorkoutSessionStatus;
use App\Models\Exercise;
use App\Models\PerformedSet;
use App\Models\RoutineExercise;
use App\Models\SessionExercise;
use App\Models\User;
use App\Models\WorkoutRoutine;
use App\Models\WorkoutSession;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('casts domain enums and exposes the complete relationship graph', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->personal($user)->create([
        'modality' => Modality::Calisthenics,
        'measurement_type' => MeasurementType::Duration,
    ]);
    $routine = WorkoutRoutine::factory()->for($user)->create([
        'primary_modality' => Modality::Gym,
    ]);
    $routineExercise = RoutineExercise::factory()
        ->for($routine)
        ->for($exercise)
        ->create();
    $session = WorkoutSession::factory()->for($user)->for($routine)->create([
        'routine_name_snapshot' => $routine->name,
        'primary_modality_snapshot' => Modality::Gym,
    ]);
    $sessionExercise = SessionExercise::factory()
        ->for($session)
        ->for($exercise)
        ->create([
            'exercise_name_snapshot' => $exercise->name,
            'measurement_type_snapshot' => MeasurementType::Duration,
        ]);
    $performedSet = PerformedSet::factory()->for($sessionExercise)->create([
        'duration_seconds' => 30,
        'repetitions' => null,
    ]);

    expect($exercise->modality)->toBe(Modality::Calisthenics)
        ->and($exercise->measurement_type)->toBe(MeasurementType::Duration)
        ->and($routine->routineExercises)->toHaveCount(1)
        ->and($routineExercise->exercise->is($exercise))->toBeTrue()
        ->and($session->status)->toBe(WorkoutSessionStatus::InProgress)
        ->and($session->sessionExercises)->toHaveCount(1)
        ->and($sessionExercise->performedSets)->toHaveCount(1)
        ->and($performedSet->sessionExercise->is($sessionExercise))->toBeTrue();
});

it('allows a routine to mix exercises from both modalities', function () {
    $user = User::factory()->create();
    $routine = WorkoutRoutine::factory()->for($user)->create([
        'primary_modality' => Modality::Gym,
    ]);
    $gymExercise = Exercise::factory()->global()->create(['modality' => Modality::Gym]);
    $calisthenicsExercise = Exercise::factory()->global()->create(['modality' => Modality::Calisthenics]);

    RoutineExercise::factory()->for($routine)->for($gymExercise)->create(['position' => 1]);
    RoutineExercise::factory()->for($routine)->for($calisthenicsExercise)->create(['position' => 2]);

    expect($routine->fresh()->routineExercises->pluck('exercise.modality')->all())
        ->toEqual([Modality::Gym, Modality::Calisthenics]);
});

it('enforces unique positions and performed-set idempotency keys', function () {
    $routine = WorkoutRoutine::factory()->create();
    $routineExercise = RoutineExercise::factory()->for($routine)->create(['position' => 1]);

    expect(fn () => RoutineExercise::factory()->for($routine)->create(['position' => 1]))
        ->toThrow(QueryException::class);

    $sessionExercise = SessionExercise::factory()->create();
    $performedSet = PerformedSet::factory()->for($sessionExercise)->create([
        'set_number' => 1,
    ]);

    expect(fn () => PerformedSet::factory()->for($sessionExercise)->create(['set_number' => 1]))
        ->toThrow(QueryException::class);

    expect(fn () => PerformedSet::factory()->create([
        'session_exercise_id' => $sessionExercise->id,
        'set_number' => 2,
        'client_request_id' => $performedSet->client_request_id,
    ]))->toThrow(QueryException::class);
});

it('allows only one in-progress session per user while preserving completed history', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->create(['status' => WorkoutSessionStatus::InProgress]);

    expect(fn () => WorkoutSession::factory()->for($user)->create(['status' => WorkoutSessionStatus::InProgress]))
        ->toThrow(QueryException::class);

    WorkoutSession::factory()->completed()->for($user)->create();
    WorkoutSession::factory()->completed()->for($user)->create();

    expect($user->fresh()->workoutSessions()->count())->toBe(3);
});

it('keeps session exercise history when its source exercise is removed', function () {
    $exercise = Exercise::factory()->create();
    $sessionExercise = SessionExercise::factory()->create([
        'exercise_id' => $exercise->id,
        'exercise_name_snapshot' => $exercise->name,
    ]);

    $exercise->delete();

    $sessionExercise->refresh();

    expect($sessionExercise->exercise_id)->toBeNull()
        ->and($sessionExercise->exercise_name_snapshot)->toBeString()->not->toBeEmpty();
});

it('keeps snapshots independent from later routine and exercise edits', function () {
    $exercise = Exercise::factory()->create(['name' => 'Original Exercise']);
    $routine = WorkoutRoutine::factory()->create(['name' => 'Original Routine']);
    $session = WorkoutSession::factory()->for($routine)->create([
        'routine_name_snapshot' => $routine->name,
    ]);
    $sessionExercise = SessionExercise::factory()->for($session)->for($exercise)->create([
        'exercise_name_snapshot' => $exercise->name,
    ]);

    $routine->update(['name' => 'Changed Routine']);
    $exercise->update(['name' => 'Changed Exercise']);

    expect($session->refresh()->routine_name_snapshot)->toBe('Original Routine')
        ->and($sessionExercise->refresh()->exercise_name_snapshot)->toBe('Original Exercise');
});
