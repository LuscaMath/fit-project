<?php

namespace App\Http\Controllers;

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Http\Requests\ExerciseIndexRequest;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(ExerciseIndexRequest $request): View
    {
        Gate::authorize('viewAny', Exercise::class);

        return $this->renderIndex($request);
    }

    public function modalityIndex(ExerciseIndexRequest $request, string $modality): View
    {
        Gate::authorize('viewAny', Exercise::class);

        $selectedModality = Modality::tryFrom($modality);

        abort_unless($selectedModality, 404);

        return $this->renderIndex($request, $selectedModality);
    }

    private function renderIndex(ExerciseIndexRequest $request, ?Modality $selectedModality = null): View
    {
        $filters = $request->validated();

        if ($selectedModality) {
            $filters['modality'] = $selectedModality->value;
        }

        $query = $this->visibleExercisesQuery($request, $selectedModality)
            ->when($selectedModality === null ? ($filters['modality'] ?? null) : null, fn ($query, $modality) => $query->where('modality', $modality))
            ->when($filters['muscle_group'] ?? null, fn ($query, $muscleGroup) => $query->where('muscle_group', $muscleGroup))
            ->when($filters['equipment'] ?? null, fn ($query, $equipment) => $query->where('equipment', $equipment))
            ->orderBy('name');

        $exercises = $query->paginate(12)->withQueryString();
        $muscleGroups = $this->visibleExercisesQuery($request, $selectedModality)
            ->distinct()
            ->orderBy('muscle_group')
            ->pluck('muscle_group');
        $equipmentOptions = $this->visibleExercisesQuery($request, $selectedModality)
            ->whereNotNull('equipment')
            ->distinct()
            ->orderBy('equipment')
            ->pluck('equipment');

        return view('exercises.index', [
            'exercises' => $exercises,
            'filters' => $filters,
            'modalities' => $selectedModality ? [$selectedModality] : Modality::cases(),
            'muscleGroups' => $muscleGroups,
            'equipmentOptions' => $equipmentOptions,
            'selectedModality' => $selectedModality,
            'libraryRoute' => $selectedModality ? 'modalities.exercises' : 'exercises.index',
            'libraryRouteParameters' => $selectedModality ? ['modality' => $selectedModality->value] : [],
        ]);
    }

    private function visibleExercisesQuery(Request $request, ?Modality $selectedModality = null): Builder
    {
        return Exercise::query()
            ->where(function ($query) use ($request): void {
                $query->whereNull('user_id')->orWhere('user_id', $request->user()->id);
            })
            ->when($selectedModality, fn ($query, Modality $modality) => $query->where('modality', $modality->value));
    }

    public function create(): View
    {
        Gate::authorize('create', Exercise::class);

        return view('exercises.create', [
            'exercise' => new Exercise([
                'modality' => Modality::Gym,
            ]),
            'modalities' => Modality::cases(),
            'measurementTypes' => MeasurementType::cases(),
        ]);
    }

    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        Gate::authorize('create', Exercise::class);

        $exercise = $request->user()->exercises()->create($request->validated());

        return redirect()->route('exercises.show', $exercise)
            ->with('status', 'Exercício pessoal criado.');
    }

    public function show(Exercise $exercise): View
    {
        Gate::authorize('view', $exercise);

        return view('exercises.show', compact('exercise'));
    }

    public function edit(Exercise $exercise): View
    {
        Gate::authorize('update', $exercise);

        return view('exercises.edit', [
            'exercise' => $exercise,
            'modalities' => Modality::cases(),
            'measurementTypes' => MeasurementType::cases(),
        ]);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        Gate::authorize('update', $exercise);
        $exercise->update($request->validated());

        return redirect()->route('exercises.show', $exercise)
            ->with('status', 'Exercício pessoal atualizado.');
    }
}
