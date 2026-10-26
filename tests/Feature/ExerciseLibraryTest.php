<?php

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to access the exercise library', function () {
    $this->get(route('exercises.index'))
        ->assertRedirect(route('login'));
});

it('lists global exercises and only the authenticated user personal exercises', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $global = Exercise::factory()->global()->create(['name' => 'Exercício global']);
    $personal = Exercise::factory()->personal($user)->create(['name' => 'Exercício pessoal']);
    Exercise::factory()->personal($otherUser)->create(['name' => 'Exercício privado']);

    $response = $this->actingAs($user)->get(route('exercises.index'));

    $response->assertOk()
        ->assertSee($global->name)
        ->assertSee($personal->name)
        ->assertDontSee('Exercício privado');
});

it('filters the library by modality, muscle group and equipment', function () {
    $user = User::factory()->create();
    Exercise::factory()->global()->create([
        'name' => 'Barra de costas',
        'modality' => Modality::Calisthenics,
        'muscle_group' => 'costas',
        'equipment' => 'barra',
    ]);
    Exercise::factory()->global()->create([
        'name' => 'Agachamento de pernas',
        'modality' => Modality::Gym,
        'muscle_group' => 'pernas',
        'equipment' => 'barra',
    ]);

    $response = $this->actingAs($user)->get(route('exercises.index', [
        'modality' => Modality::Calisthenics->value,
        'muscle_group' => 'costas',
        'equipment' => 'barra',
    ]));

    $response->assertOk()
        ->assertSee('Barra de costas')
        ->assertDontSee('Agachamento de pernas');
});

it('shows instructions and embeds an allowed YouTube video', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->global()->create([
        'instructions' => 'Mantenha o movimento controlado.',
        'video_url' => 'https://www.youtube.com/watch?v=IODxDxX7oi4',
    ]);

    $this->actingAs($user)->get(route('exercises.show', $exercise))
        ->assertOk()
        ->assertSee('Mantenha o movimento controlado.')
        ->assertSee('https://www.youtube.com/embed/IODxDxX7oi4', false);
});

it('creates and updates a personal exercise for its owner', function () {
    $user = User::factory()->create();

    $createResponse = $this->actingAs($user)->post(route('exercises.store'), [
        'name' => 'Prancha lateral',
        'modality' => Modality::Calisthenics->value,
        'measurement_type' => MeasurementType::Duration->value,
        'muscle_group' => 'abdômen',
        'equipment' => '',
        'instructions' => 'Mantenha o corpo alinhado.',
        'video_url' => 'https://youtu.be/IODxDxX7oi4',
    ]);

    $exercise = Exercise::query()->where('name', 'Prancha lateral')->firstOrFail();

    $createResponse->assertRedirect(route('exercises.show', $exercise));
    expect($exercise->user_id)->toBe($user->id);

    $updateResponse = $this->actingAs($user)->put(route('exercises.update', $exercise), [
        'name' => 'Prancha lateral atualizada',
        'modality' => Modality::Calisthenics->value,
        'measurement_type' => MeasurementType::Duration->value,
        'muscle_group' => 'core',
        'equipment' => null,
        'instructions' => 'Nova instrução.',
        'video_url' => null,
    ]);

    $updateResponse->assertRedirect(route('exercises.show', $exercise));
    expect($exercise->refresh()->name)->toBe('Prancha lateral atualizada');
});

it('denies another user access to a personal exercise', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $exercise = Exercise::factory()->personal($owner)->create();

    $this->actingAs($otherUser)->get(route('exercises.show', $exercise))->assertForbidden();
    $this->actingAs($otherUser)->get(route('exercises.edit', $exercise))->assertForbidden();
    $this->actingAs($otherUser)->put(route('exercises.update', $exercise), [
        'name' => 'Tentativa indevida',
        'modality' => Modality::Gym->value,
        'measurement_type' => MeasurementType::Repetitions->value,
        'muscle_group' => 'peito',
        'instructions' => 'Não deve salvar.',
    ])->assertForbidden();

    expect($exercise->refresh()->name)->not->toBe('Tentativa indevida');
});

it('rejects unsupported video providers and invalid exercise data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from(route('exercises.create'))->post(route('exercises.store'), [
        'name' => '',
        'modality' => 'invalid',
        'measurement_type' => MeasurementType::Repetitions->value,
        'muscle_group' => 'peito',
        'instructions' => 'Teste',
        'video_url' => 'https://example.com/video',
    ]);

    $response->assertRedirect(route('exercises.create'))
        ->assertSessionHasErrors(['name', 'modality', 'video_url']);
    expect(Exercise::query()->count())->toBe(0);
});

it('seeds a small global catalog without personal ownership', function () {
    $this->seed(ExerciseSeeder::class);

    expect(Exercise::query()->count())->toBe(5)
        ->and(Exercise::query()->whereNotNull('user_id')->count())->toBe(0);
});
