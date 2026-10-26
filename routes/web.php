<?php

use App\Enums\Modality;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ModalityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkoutRoutineController;
use App\Http\Controllers\WorkoutSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    $modalities = array_map(fn (Modality $modality): string => $modality->value, Modality::cases());

    Route::get('/modalidades', [ModalityController::class, 'index'])->name('modalities.index');
    Route::post('/modalidades/{modality}', [ModalityController::class, 'select'])
        ->whereIn('modality', $modalities)
        ->name('modalities.select');
    Route::get('/{modality}/exercises', [ExerciseController::class, 'modalityIndex'])
        ->whereIn('modality', $modalities)
        ->name('modalities.exercises');
    Route::get('/rotinas', [WorkoutRoutineController::class, 'index'])->name('routines.index');
    Route::get('/rotinas/create', [WorkoutRoutineController::class, 'create'])->name('routines.create');
    Route::post('/rotinas', [WorkoutRoutineController::class, 'store'])->name('routines.store');
    Route::get('/rotinas/{routine}', [WorkoutRoutineController::class, 'show'])->name('routines.show');
    Route::get('/rotinas/{routine}/edit', [WorkoutRoutineController::class, 'edit'])->name('routines.edit');
    Route::put('/rotinas/{routine}', [WorkoutRoutineController::class, 'update'])->name('routines.update');
    Route::patch('/rotinas/{routine}/archive', [WorkoutRoutineController::class, 'archive'])->name('routines.archive');
    Route::delete('/rotinas/{routine}', [WorkoutRoutineController::class, 'destroy'])->name('routines.destroy');
    Route::post('/rotinas/{routine}/sessoes', [WorkoutSessionController::class, 'start'])->name('sessions.start');
    Route::get('/{modality}/rotinas', [WorkoutRoutineController::class, 'modalityIndex'])
        ->whereIn('modality', $modalities)
        ->name('modalities.routines');
    Route::get('/sessao-atual', [WorkoutSessionController::class, 'current'])->name('sessions.current');
    Route::get('/sessoes', [WorkoutSessionController::class, 'index'])->name('sessions.index');
    Route::post('/sessoes/{session}/finalizar', [WorkoutSessionController::class, 'finish'])->name('sessions.finish');
    Route::post('/sessoes/{session}/exercicios/{sessionExercise}/series', [WorkoutSessionController::class, 'storeSet'])->name('sessions.sets.store');
    Route::patch('/sessoes/{session}/series/{performedSet}', [WorkoutSessionController::class, 'updateSet'])->name('sessions.sets.update');
    Route::delete('/sessoes/{session}/series/{performedSet}', [WorkoutSessionController::class, 'destroySet'])->name('sessions.sets.destroy');
    Route::get('/sessoes/{session}', [WorkoutSessionController::class, 'show'])->name('sessions.show');

    Route::resource('exercises', ExerciseController::class)->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
    ]);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
