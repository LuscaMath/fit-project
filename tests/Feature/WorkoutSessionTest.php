<?php

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Enums\WorkoutSessionStatus;
use App\Models\Exercise;
use App\Models\PerformedSet;
use App\Models\SessionExercise;
use App\Models\User;
use App\Models\WorkoutRoutine;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('requires authentication to start a session', function () {
    $routine = WorkoutRoutine::factory()->create();

    $this->post(route('sessions.start', $routine))
        ->assertRedirect(route('login'));
});

it('starts a session and snapshots the routine parameters and exercise order', function () {
    $user = User::factory()->create();
    $gymExercise = Exercise::factory()->global()->create([
        'name' => 'Supino snapshot',
        'modality' => Modality::Gym,
        'measurement_type' => MeasurementType::Repetitions,
    ]);
    $calisthenicsExercise = Exercise::factory()->global()->create([
        'name' => 'Prancha snapshot',
        'modality' => Modality::Calisthenics,
        'measurement_type' => MeasurementType::Duration,
    ]);
    $routine = WorkoutRoutine::factory()->create([
        'user_id' => $user->id,
        'name' => 'Rotina snapshot',
        'primary_modality' => Modality::Gym,
    ]);
    $routine->routineExercises()->create([
        'exercise_id' => $gymExercise->id,
        'position' => 2,
        'planned_sets' => 4,
        'target_reps' => 8,
        'target_duration_seconds' => null,
        'target_load_kg' => 60,
        'rest_seconds' => 120,
    ]);
    $routine->routineExercises()->create([
        'exercise_id' => $calisthenicsExercise->id,
        'position' => 1,
        'planned_sets' => 3,
        'target_reps' => null,
        'target_duration_seconds' => 45,
        'target_load_kg' => null,
        'rest_seconds' => 60,
    ]);

    $response = $this->actingAs($user)->post(route('sessions.start', $routine));
    $session = WorkoutSession::query()->where('user_id', $user->id)->firstOrFail();

    $response->assertRedirect(route('sessions.show', $session));
    expect($session->status)->toBe(WorkoutSessionStatus::InProgress);
    expect($session->routine_name_snapshot)->toBe('Rotina snapshot');
    expect($session->primary_modality_snapshot)->toBe(Modality::Gym);
    expect($session->sessionExercises()->orderBy('position')->pluck('exercise_name_snapshot')->all())
        ->toBe(['Prancha snapshot', 'Supino snapshot']);
    $this->assertDatabaseHas('session_exercises', [
        'workout_session_id' => $session->id,
        'position' => 2,
        'planned_sets' => 4,
        'target_reps' => 8,
        'target_load_kg' => 60,
    ]);
});

it('returns the existing active session when starting again', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create();
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id]);
    $routine->routineExercises()->create([
        'exercise_id' => $exercise->id,
        'position' => 1,
        'planned_sets' => 3,
        'target_reps' => 10,
        'target_duration_seconds' => null,
        'target_load_kg' => null,
        'rest_seconds' => 60,
    ]);

    $first = $this->actingAs($user)->post(route('sessions.start', $routine));
    $session = WorkoutSession::query()->where('user_id', $user->id)->firstOrFail();
    $second = $this->actingAs($user)->post(route('sessions.start', $routine));

    $first->assertRedirect(route('sessions.show', $session));
    $second->assertRedirect(route('sessions.show', $session));
    expect(WorkoutSession::query()->where('user_id', $user->id)->count())->toBe(1);
    expect(SessionExercise::query()->where('workout_session_id', $session->id)->count())->toBe(1);
});

it('keeps the session snapshot after the routine and exercise are changed', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create(['name' => 'Nome original']);
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id, 'name' => 'Rotina original']);
    $routine->routineExercises()->create([
        'exercise_id' => $exercise->id,
        'position' => 1,
        'planned_sets' => 3,
        'target_reps' => 10,
        'target_duration_seconds' => null,
        'target_load_kg' => 20,
        'rest_seconds' => 90,
    ]);

    $this->actingAs($user)->post(route('sessions.start', $routine));
    $session = WorkoutSession::query()->where('user_id', $user->id)->firstOrFail();
    $routine->update(['name' => 'Rotina alterada']);
    $exercise->update(['name' => 'Nome alterado']);

    $session->refresh();
    $sessionExercise = $session->sessionExercises()->firstOrFail();
    expect($session->routine_name_snapshot)->toBe('Rotina original');
    expect($sessionExercise->exercise_name_snapshot)->toBe('Nome original');
});

it('shows one exercise at a time and allows previous and next navigation', function () {
    $user = User::factory()->create();
    $first = Exercise::factory()->global()->create(['name' => 'Primeiro exercício']);
    $second = Exercise::factory()->global()->create(['name' => 'Segundo exercício']);
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id]);
    foreach ([$first, $second] as $position => $exercise) {
        $routine->routineExercises()->create([
            'exercise_id' => $exercise->id,
            'position' => $position + 1,
            'planned_sets' => 3,
            'target_reps' => 10,
            'target_duration_seconds' => null,
            'target_load_kg' => null,
            'rest_seconds' => 60,
        ]);
    }

    $this->actingAs($user)->post(route('sessions.start', $routine));
    $session = WorkoutSession::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get(route('sessions.show', $session))
        ->assertOk()
        ->assertSee('Primeiro exercício')
        ->assertSee('Registrar série')
        ->assertSee('Nenhuma série registrada para este exercício.')
        ->assertDontSee('Segundo exercício')
        ->assertSee('position=2', false);
    $this->actingAs($user)->get(route('sessions.show', ['session' => $session, 'position' => 2]))
        ->assertOk()
        ->assertSee('Segundo exercício')
        ->assertDontSee('Primeiro exercício');
});

it('supports resuming the active session and protects it from another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exercise = Exercise::factory()->global()->create();
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id]);
    $routine->routineExercises()->create([
        'exercise_id' => $exercise->id,
        'position' => 1,
        'planned_sets' => 3,
        'target_reps' => 10,
        'target_duration_seconds' => null,
        'target_load_kg' => null,
        'rest_seconds' => 60,
    ]);
    $this->actingAs($user)->post(route('sessions.start', $routine));
    $session = WorkoutSession::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($user)->get(route('sessions.current'))
        ->assertRedirect(route('sessions.show', $session));
    $this->actingAs($otherUser)->get(route('sessions.show', $session))->assertForbidden();
});

it('rejects starting an archived routine', function () {
    $user = User::factory()->create();
    $routine = WorkoutRoutine::factory()->archived()->create(['user_id' => $user->id]);

    $this->actingAs($user)->from(route('routines.show', $routine))
        ->post(route('sessions.start', $routine))
        ->assertSessionHasErrors('routine');

    expect(WorkoutSession::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('redirects to routines when no active session exists', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('sessions.current'))
        ->assertRedirect(route('routines.index'))
        ->assertSessionHas('status', 'Nenhuma sessão em andamento.');
});

it('keeps an active session valid after its routine is deleted and allows another routine', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $routine = WorkoutRoutine::factory()->create(['user_id' => $user->id, 'name' => 'Rotina removida']);
    $routine->routineExercises()->create([
        'exercise_id' => $exercise->id,
        'position' => 1,
        'planned_sets' => 3,
        'target_reps' => 10,
        'target_duration_seconds' => null,
        'target_load_kg' => null,
        'rest_seconds' => 60,
    ]);

    $this->actingAs($user)->post(route('sessions.start', $routine));
    $session = WorkoutSession::query()->where('user_id', $user->id)->firstOrFail();
    $this->actingAs($user)->delete(route('routines.destroy', $routine))->assertRedirect(route('routines.index'));

    expect($session->fresh()->workout_routine_id)->toBeNull();
    $this->actingAs($user)->get(route('sessions.show', $session))
        ->assertOk()
        ->assertSee('Voltar para rotinas');

    $newRoutineResponse = $this->actingAs($user)->post(route('routines.store'), [
        'name' => 'Nova rotina',
        'primary_modality' => Modality::Gym->value,
        'exercises' => [[
            'position' => 1,
            'exercise_id' => $exercise->id,
            'planned_sets' => 3,
            'target_reps' => 10,
            'target_duration_seconds' => null,
            'target_load_kg' => null,
            'rest_seconds' => 60,
        ]],
    ]);

    $newRoutineResponse->assertRedirect();
    $this->assertDatabaseHas('workout_routines', ['name' => 'Nova rotina', 'user_id' => $user->id]);
});

it('records, retries, updates and deletes a performed set idempotently', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Repetitions]);
    $session = WorkoutSession::factory()->create(['user_id' => $user->id]);
    $sessionExercise = SessionExercise::factory()->create([
        'workout_session_id' => $session->id,
        'exercise_id' => $exercise->id,
        'measurement_type_snapshot' => MeasurementType::Repetitions,
        'target_duration_seconds' => null,
    ]);
    $clientRequestId = (string) Str::uuid();
    $payload = [
        'set_number' => 1,
        'repetitions' => 10,
        'duration_seconds' => null,
        'load_kg' => 20,
        'client_request_id' => $clientRequestId,
    ];

    $this->actingAs($user)->postJson(route('sessions.sets.store', [$session, $sessionExercise]), $payload)
        ->assertCreated()
        ->assertJsonPath('data.set_number', 1);
    $this->actingAs($user)->postJson(route('sessions.sets.store', [$session, $sessionExercise]), $payload)
        ->assertOk()
        ->assertJsonPath('data.set_number', 1);

    expect(PerformedSet::query()->where('client_request_id', $clientRequestId)->count())->toBe(1);

    $this->actingAs($user)->postJson(route('sessions.sets.store', [$session, $sessionExercise]), [
        ...$payload,
        'repetitions' => 11,
    ])->assertStatus(409);

    $performedSet = PerformedSet::query()->where('client_request_id', $clientRequestId)->firstOrFail();
    $this->actingAs($user)->patchJson(route('sessions.sets.update', [$session, $performedSet]), [
        'repetitions' => 12,
        'duration_seconds' => null,
        'load_kg' => 22.5,
    ])->assertOk()->assertJsonPath('data.repetitions', 12);

    $this->actingAs($user)->deleteJson(route('sessions.sets.destroy', [$session, $performedSet]))
        ->assertOk();
    $this->assertDatabaseMissing('performed_sets', ['id' => $performedSet->id]);
});

it('validates the performed metric and blocks set mutations outside the owner active session', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $exercise = Exercise::factory()->global()->create(['measurement_type' => MeasurementType::Duration]);
    $session = WorkoutSession::factory()->create(['user_id' => $owner->id]);
    $sessionExercise = SessionExercise::factory()->create([
        'workout_session_id' => $session->id,
        'exercise_id' => $exercise->id,
        'measurement_type_snapshot' => MeasurementType::Duration,
        'target_reps' => null,
        'target_duration_seconds' => 60,
    ]);
    $payload = [
        'set_number' => 1,
        'repetitions' => 10,
        'duration_seconds' => null,
        'load_kg' => null,
        'client_request_id' => (string) Str::uuid(),
    ];

    $this->actingAs($owner)->postJson(route('sessions.sets.store', [$session, $sessionExercise]), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['repetitions', 'duration_seconds']);
    $this->actingAs($otherUser)->postJson(route('sessions.sets.store', [$session, $sessionExercise]), [
        ...$payload,
        'repetitions' => null,
        'duration_seconds' => 60,
    ])->assertForbidden();

    $completedSession = WorkoutSession::factory()->completed()->create(['user_id' => $owner->id]);
    $completedExercise = SessionExercise::factory()->create([
        'workout_session_id' => $completedSession->id,
        'exercise_id' => $exercise->id,
        'measurement_type_snapshot' => MeasurementType::Duration,
        'target_reps' => null,
        'target_duration_seconds' => 60,
    ]);
    $this->actingAs($owner)->postJson(route('sessions.sets.store', [$completedSession, $completedExercise]), [
        'set_number' => 1,
        'repetitions' => null,
        'duration_seconds' => 60,
        'load_kg' => null,
        'client_request_id' => (string) Str::uuid(),
    ])->assertForbidden();
});

it('finishes an active session with pending exercises and makes it read-only', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create();
    $session = WorkoutSession::factory()->create(['user_id' => $user->id]);
    $sessionExercise = SessionExercise::factory()->create([
        'workout_session_id' => $session->id,
        'exercise_id' => $exercise->id,
        'exercise_name_snapshot' => $exercise->name,
    ]);

    $this->actingAs($user)
        ->post(route('sessions.finish', $session))
        ->assertRedirect(route('sessions.show', $session));

    $session->refresh();
    expect($session->status)->toBe(WorkoutSessionStatus::Completed)
        ->and($session->finished_at)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('sessions.show', $session))
        ->assertOk()
        ->assertSee('Sessão finalizada')
        ->assertSee('Somente leitura')
        ->assertDontSee('Registrar série')
        ->assertDontSee('Editar')
        ->assertDontSee('Excluir');

    $this->actingAs($user)
        ->postJson(route('sessions.sets.store', [$session, $sessionExercise]), [
            'set_number' => 1,
            'repetitions' => 10,
            'duration_seconds' => null,
            'load_kg' => null,
            'client_request_id' => (string) Str::uuid(),
        ])
        ->assertForbidden();
});

it('finishing an already completed session is idempotent and protected by ownership', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $session = WorkoutSession::factory()->completed()->create(['user_id' => $user->id]);
    $finishedAt = $session->finished_at;

    $this->actingAs($otherUser)
        ->post(route('sessions.finish', $session))
        ->assertForbidden();
    $this->actingAs($user)
        ->post(route('sessions.finish', $session))
        ->assertRedirect(route('sessions.show', $session));

    expect($session->fresh()->finished_at->equalTo($finishedAt))->toBeTrue();
});

it('shows only the users sessions and latest completed performance in history', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $exercise = Exercise::factory()->global()->create(['name' => 'Agachamento histórico']);
    $olderSession = WorkoutSession::factory()->completed()->create(['user_id' => $user->id, 'started_at' => now()->subDay()]);
    $olderExercise = SessionExercise::factory()->create([
        'workout_session_id' => $olderSession->id,
        'exercise_id' => $exercise->id,
        'exercise_name_snapshot' => $exercise->name,
    ]);
    $olderExercise->performedSets()->create([
        'set_number' => 1,
        'repetitions' => 8,
        'duration_seconds' => null,
        'load_kg' => 40,
        'client_request_id' => (string) Str::uuid(),
        'completed_at' => now()->subDay(),
    ]);
    $latestSession = WorkoutSession::factory()->completed()->create(['user_id' => $user->id]);
    $latestExercise = SessionExercise::factory()->create([
        'workout_session_id' => $latestSession->id,
        'exercise_id' => $exercise->id,
        'exercise_name_snapshot' => $exercise->name,
    ]);
    $latestExercise->performedSets()->create([
        'set_number' => 1,
        'repetitions' => 10,
        'duration_seconds' => null,
        'load_kg' => 50,
        'client_request_id' => (string) Str::uuid(),
        'completed_at' => now(),
    ]);
    $otherSession = WorkoutSession::factory()->completed()->create(['user_id' => $otherUser->id, 'routine_name_snapshot' => 'Sessão de outra pessoa']);
    SessionExercise::factory()->create(['workout_session_id' => $otherSession->id]);

    $this->actingAs($user)
        ->get(route('sessions.index'))
        ->assertOk()
        ->assertSee($latestSession->routine_name_snapshot)
        ->assertDontSee('Sessão de outra pessoa')
        ->assertSee('Agachamento histórico')
        ->assertSee('10 repetições')
        ->assertSee('50.00 kg');

    $this->actingAs($otherUser)
        ->get(route('sessions.index'))
        ->assertOk()
        ->assertDontSee($latestSession->routine_name_snapshot)
        ->assertDontSee('Agachamento histórico');
});
