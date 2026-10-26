<?php

use App\Enums\Modality;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to choose a modality', function () {
    $this->get(route('modalities.index'))
        ->assertRedirect(route('login'));
});

it('shows the available modalities to an authenticated user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('modalities.index'))
        ->assertOk()
        ->assertSee('Academia')
        ->assertSee('Calistenia');
});

it('persists the selected modality and redirects to its library', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('modalities.select', Modality::Gym->value))
        ->assertRedirect(route('modalities.exercises', Modality::Gym->value))
        ->assertSessionHas('current_modality', Modality::Gym->value);

    $this->actingAs($user)
        ->withSession(['current_modality' => Modality::Gym->value])
        ->get(route('modalities.index'))
        ->assertSee('Continuar em Academia')
        ->assertSee(route('modalities.exercises', Modality::Gym->value), false);
});

it('filters the mode library while preserving global and personal visibility rules', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Exercise::factory()->global()->create([
        'name' => 'Supino global',
        'modality' => Modality::Gym,
    ]);
    Exercise::factory()->personal($user)->create([
        'name' => 'Agachamento pessoal',
        'modality' => Modality::Gym,
    ]);
    Exercise::factory()->global()->create([
        'name' => 'Barra global',
        'modality' => Modality::Calisthenics,
    ]);
    Exercise::factory()->personal($otherUser)->create([
        'name' => 'Privado de outro usuário',
        'modality' => Modality::Gym,
    ]);

    $this->actingAs($user)
        ->get(route('modalities.exercises', Modality::Gym->value))
        ->assertOk()
        ->assertSee('Supino global')
        ->assertSee('Agachamento pessoal')
        ->assertDontSee('Barra global')
        ->assertDontSee('Privado de outro usuário');
});

it('allows switching the persisted modality', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['current_modality' => Modality::Gym->value])
        ->post(route('modalities.select', Modality::Calisthenics->value))
        ->assertRedirect(route('modalities.exercises', Modality::Calisthenics->value))
        ->assertSessionHas('current_modality', Modality::Calisthenics->value);
});

it('rejects an unsupported modality', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/modalidades/unsupported')
        ->assertNotFound();

    $this->actingAs($user)
        ->get('/unsupported/exercises')
        ->assertNotFound();
});
