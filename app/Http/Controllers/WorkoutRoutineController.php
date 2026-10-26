<?php

namespace App\Http\Controllers;

use App\Enums\Modality;
use App\Http\Requests\RoutineIndexRequest;
use App\Http\Requests\StoreWorkoutRoutineRequest;
use App\Http\Requests\UpdateWorkoutRoutineRequest;
use App\Models\Exercise;
use App\Models\WorkoutRoutine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkoutRoutineController extends Controller
{
    public function index(RoutineIndexRequest $request): View
    {
        Gate::authorize('viewAny', WorkoutRoutine::class);

        return $this->renderIndex($request);
    }

    public function modalityIndex(RoutineIndexRequest $request, string $modality): View
    {
        Gate::authorize('viewAny', WorkoutRoutine::class);

        $selectedModality = Modality::tryFrom($modality);

        abort_unless($selectedModality, 404);

        return $this->renderIndex($request, $selectedModality);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', WorkoutRoutine::class);

        $selectedModality = Modality::tryFrom($request->query('primary_modality'))
            ?? Modality::tryFrom($request->session()->get('current_modality'))
            ?? Modality::Gym;

        return view('routines.create', [
            'routine' => new WorkoutRoutine(['primary_modality' => $selectedModality]),
            'exercises' => $this->visibleExercises($request->user()),
            'modalities' => Modality::cases(),
        ]);
    }

    public function store(StoreWorkoutRoutineRequest $request): RedirectResponse
    {
        Gate::authorize('create', WorkoutRoutine::class);

        $routine = DB::transaction(function () use ($request): WorkoutRoutine {
            $data = $request->validated();
            $items = $data['exercises'];
            unset($data['exercises']);

            $routine = $request->user()->workoutRoutines()->create($data);
            $this->replaceRoutineExercises($routine, $items);

            return $routine;
        });

        return redirect()->route('routines.show', $routine)
            ->with('status', 'Rotina criada com sucesso.');
    }

    public function show(WorkoutRoutine $routine): View
    {
        Gate::authorize('view', $routine);
        $routine->load('routineExercises.exercise');

        return view('routines.show', compact('routine'));
    }

    public function edit(WorkoutRoutine $routine): View
    {
        Gate::authorize('update', $routine);
        $routine->load('routineExercises.exercise');

        return view('routines.edit', [
            'routine' => $routine,
            'exercises' => $this->visibleExercises(request()->user()),
            'modalities' => Modality::cases(),
        ]);
    }

    public function update(UpdateWorkoutRoutineRequest $request, WorkoutRoutine $routine): RedirectResponse
    {
        Gate::authorize('update', $routine);

        DB::transaction(function () use ($request, $routine): void {
            $data = $request->validated();
            $items = $data['exercises'];
            unset($data['exercises']);

            $routine->update($data);
            $this->replaceRoutineExercises($routine, $items);
        });

        return redirect()->route('routines.show', $routine)
            ->with('status', 'Rotina atualizada com sucesso.');
    }

    public function archive(WorkoutRoutine $routine): RedirectResponse
    {
        Gate::authorize('update', $routine);
        $routine->update(['is_active' => ! $routine->is_active]);

        return redirect()->back()->with('status', $routine->is_active ? 'Rotina restaurada.' : 'Rotina arquivada.');
    }

    public function destroy(WorkoutRoutine $routine): RedirectResponse
    {
        Gate::authorize('delete', $routine);
        $routine->delete();

        return redirect()->route('routines.index')->with('status', 'Rotina excluída.');
    }

    private function renderIndex(RoutineIndexRequest $request, ?Modality $selectedModality = null): View
    {
        $includeArchived = $request->boolean('include_archived');
        $routines = WorkoutRoutine::query()
            ->where('user_id', $request->user()->id)
            ->when($selectedModality, fn ($query, Modality $modality) => $query->where('primary_modality', $modality->value))
            ->when(! $includeArchived, fn ($query) => $query->where('is_active', true))
            ->withCount('routineExercises')
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('routines.index', [
            'routines' => $routines,
            'selectedModality' => $selectedModality,
            'includeArchived' => $includeArchived,
            'libraryRoute' => $selectedModality ? 'modalities.routines' : 'routines.index',
            'libraryRouteParameters' => $selectedModality ? ['modality' => $selectedModality->value] : [],
        ]);
    }

    private function visibleExercises($user)
    {
        return Exercise::query()
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('modality')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function replaceRoutineExercises(WorkoutRoutine $routine, array $items): void
    {
        $routine->routineExercises()->delete();

        collect($items)
            ->sortBy(fn (array $item): int => (int) $item['position'])
            ->values()
            ->each(function (array $item, int $index) use ($routine): void {
                $item['position'] = $index + 1;
                $routine->routineExercises()->create($item);
            });
    }
}
