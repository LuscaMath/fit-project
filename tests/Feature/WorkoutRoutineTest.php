<?php

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutRoutine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function routineExerciseData(int $exerciseId, int $position = 1, array $overrides = []): array
{
    return array_merge([
        'position' => $position,
        'exercise_id' => $exerciseId,
        'planned_sets' => 3,
        'target_reps' => 10,
        'target_duration_seconds' => null,
        'target_load_kg' => 20,
        'rest_seconds' => 90,
        'notes' => null,
    ], $overrides);
}

it('requires authentication to access routines', function () {
    $this->get(route('routines.index'))
        ->assertRedirect(route('login'));
});

it('exposes measurement metadata for dynamic routine exercise rows', function () {
    $user = User::factory()->create();
    Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Duration]);

    $this->actingAs($user)
        ->get(route('routines.create'))
        ->assertOk()
        ->assertSee('data-measurement="reps"', false)
        ->assertSee('data-measurement="duration"', false);
});

it('creates a routine with ordered mixed-modality exercises and goals', function () {
    $user = User::factory()->create();
    $gymExercise = Exercise::factory()->global()->create([
        'name' => 'Supino planejado',
        'modality' => Modality::Gym,
        'measurement_type' => MeasurementType::Repetitions,
    ]);
    $durationExercise = Exercise::factory()->global()->create([
        'name' => 'Prancha planejada',
        'modality' => Modality::Calisthenics,
        'measurement_type' => MeasurementType::Duration,
    ]);

    $response = $this->actingAs($user)->post(route('routines.store'), [
        'name' => 'Força mista',
        'primary_modality' => Modality::Gym->value,
        'description' => 'Rotina com exercícios de ambos os modos.',
        'exercises' => [
            routineExerciseData($gymExercise->id, 2),
            routineExerciseData($durationExercise->id, 1, [
                'target_reps' => null,
                'target_duration_seconds' => 45,
            ]),
        ],
    ]);

    $routine = WorkoutRoutine::query()->where('user_id', $user->id)->firstOrFail();

    $response->assertRedirect(route('routines.show', $routine));
    expect($routine->primary_modality)->toBe(Modality::Gym);
    expect($routine->routineExercises()->orderBy('position')->pluck('exercise_id')->all())
        ->toBe([$durationExercise->id, $gymExercise->id]);
    $this->assertDatabaseHas('routine_exercises', [
        'workout_routine_id' => $routine->id,
        'exercise_id' => $durationExercise->id,
        'position' => 1,
        'target_duration_seconds' => 45,
    ]);
});

it('lists only owned routines for the selected primary modality and hides archived by default', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $gym = WorkoutRoutine::factory()->create(['user_id' => $user->id, 'name' => 'Academia ativa', 'primary_modality' => Modality::Gym]);
    WorkoutRoutine::factory()->create(['user_id' => $user->id, 'name' => 'Calistenia ativa', 'primary_modality' => Modality::Calisthenics]);
    WorkoutRoutine::factory()->archived()->create(['user_id' => $user->id, 'name' => 'Academia arquivada', 'primary_modality' => Modality::Gym]);
    WorkoutRoutine::factory()->create(['user_id' => $otherUser->id, 'name' => 'Rotina privada', 'primary_modality' => Modality::Gym]);

    $this->actingAs($user)
        ->get(route('modalities.routines', Modality::Gym->value))
        ->assertOk()
        ->assertSee('Academia ativa')
        ->assertDontSee('Calistenia ativa')
        ->assertDontSee('Academia arquivada')
        ->assertDontSee('Rotina privada');

    $this->actingAs($user)
        ->get(route('modalities.routines', [Modality::Gym->value, 'include_archived' => 1]))
        ->assertSee('Academia arquivada');
});

it('updates a routine and replaces its ordered exercise plan', function () {
    $user = User::factory()->create();
    $first = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $second = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id]);
    $routine->routineExercises()->create(routineExerciseData($first->id));

    $this->actingAs($user)->put(route('routines.update', $routine), [
        'name' => 'Rotina editada',
        'primary_modality' => Modality::Calisthenics->value,
        'description' => 'Nova descrição',
        'exercises' => [routineExerciseData($second->id, 1, ['target_reps' => 12])],
    ])->assertRedirect(route('routines.show', $routine));

    $routine->refresh();
    expect($routine->name)->toBe('Rotina editada');
    expect($routine->primary_modality)->toBe(Modality::Calisthenics);
    expect($routine->routineExercises()->pluck('exercise_id')->all())->toBe([$second->id]);
});

it('updates a one-exercise routine to two exercises', function () {
    $user = User::factory()->create();
    $first = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $second = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id]);
    $routine->routineExercises()->create(routineExerciseData($first->id));

    $this->actingAs($user)->put(route('routines.update', $routine), [
        'name' => $routine->name,
        'primary_modality' => $routine->primary_modality->value,
        'exercises' => [
            routineExerciseData($first->id, 1),
            routineExerciseData($second->id, 2, ['target_reps' => 12]),
        ],
    ])->assertRedirect(route('routines.show', $routine));

    expect($routine->routineExercises()->orderBy('position')->pluck('exercise_id')->all())
        ->toBe([$first->id, $second->id]);
});

it('archives and restores only the owner routine', function () {
    $user = User::factory()->create();
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id, 'is_active' => true]);

    $this->actingAs($user)->patch(route('routines.archive', $routine))
        ->assertRedirect()
        ->assertSessionHas('status', 'Rotina arquivada.');
    expect($routine->fresh()->is_active)->toBeFalse();

    $this->actingAs($user)->patch(route('routines.archive', $routine))
        ->assertSessionHas('status', 'Rotina restaurada.');
    expect($routine->fresh()->is_active)->toBeTrue();
});

it('deletes a routine for its owner while keeping the route protected', function () {
    $user = User::factory()->create();
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->delete(route('routines.destroy', $routine))
        ->assertRedirect(route('routines.index'))
        ->assertSessionHas('status', 'Rotina excluída.');

    $this->assertDatabaseMissing('workout_routines', ['id' => $routine->id]);
});

it('denies another user access to a routine and its mutations', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $routine = WorkoutRoutine::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($otherUser)->get(route('routines.show', $routine))->assertForbidden();
    $this->actingAs($otherUser)->get(route('routines.edit', $routine))->assertForbidden();
    $this->actingAs($otherUser)->patch(route('routines.archive', $routine))->assertForbidden();
});

it('rejects unavailable exercises and goals incompatible with measurement type', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $repetitionsExercise = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $privateExercise = Exercise::factory()->personal($otherUser)->create();

    $this->actingAs($user)->from(route('routines.create'))->post(route('routines.store'), [
        'name' => 'Inválida',
        'primary_modality' => Modality::Gym->value,
        'exercises' => [
            routineExerciseData($repetitionsExercise->id, 1, [
                'target_reps' => null,
                'target_duration_seconds' => 30,
            ]),
            routineExerciseData($privateExercise->id, 2),
        ],
    ])->assertRedirect(route('routines.create'))
        ->assertSessionHasErrors([
            'exercises.0.target_reps',
            'exercises.0.target_duration_seconds',
            'exercises.1.exercise_id',
        ]);
});

it('allows repeating an exercise at different positions', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);

    $this->actingAs($user)->post(route('routines.store'), [
        'name' => 'Repetição planejada',
        'primary_modality' => Modality::Gym->value,
        'exercises' => [
            routineExerciseData($exercise->id, 1),
            routineExerciseData($exercise->id, 2, ['target_reps' => 8]),
        ],
    ])->assertSessionHasNoErrors();

    $routine = WorkoutRoutine::query()->where('user_id', $user->id)->firstOrFail();

    expect($routine->routineExercises()->count())->toBe(2);
});
