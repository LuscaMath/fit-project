<?php

namespace App\Http\Controllers;

use App\Actions\DeletePerformedSet;
use App\Actions\FinishWorkoutSession;
use App\Actions\RecordPerformedSet;
use App\Actions\StartWorkoutSession;
use App\Actions\UpdatePerformedSet;
use App\Enums\WorkoutSessionStatus;
use App\Exceptions\IdempotencyConflictException;
use App\Http\Requests\DeletePerformedSetRequest;
use App\Http\Requests\FinishWorkoutSessionRequest;
use App\Http\Requests\StorePerformedSetRequest;
use App\Http\Requests\UpdatePerformedSetRequest;
use App\Models\PerformedSet;
use App\Models\SessionExercise;
use App\Models\WorkoutRoutine;
use App\Models\WorkoutSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkoutSessionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', WorkoutSession::class);

        $sessions = WorkoutSession::query()
            ->where('user_id', $request->user()->id)
            ->with(['sessionExercises.performedSets'])
            ->latest('started_at')
            ->paginate(12);

        $latestPerformances = PerformedSet::query()
            ->with(['sessionExercise.exercise', 'sessionExercise.workoutSession'])
            ->whereHas('sessionExercise.workoutSession', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id)
                    ->where('status', WorkoutSessionStatus::Completed->value);
            })
            ->latest('completed_at')
            ->get()
            ->unique(fn (PerformedSet $set): string => $set->sessionExercise->exercise_id
                ? 'exercise:'.$set->sessionExercise->exercise_id
                : 'snapshot:'.$set->sessionExercise->exercise_name_snapshot)
            ->values();

        return view('sessions.index', compact('sessions', 'latestPerformances'));
    }

    public function start(Request $request, WorkoutRoutine $routine, StartWorkoutSession $startWorkoutSession): RedirectResponse
    {
        Gate::authorize('view', $routine);

        $session = $startWorkoutSession->execute($request->user(), $routine);

        return redirect()->route('sessions.show', $session)
            ->with('status', 'Sessão iniciada.');
    }

    public function current(Request $request): RedirectResponse
    {
        $session = WorkoutSession::query()
            ->where('user_id', $request->user()->id)
            ->where('status', WorkoutSessionStatus::InProgress->value)
            ->latest('started_at')
            ->first();

        if (! $session) {
            return redirect()->route('routines.index')->with('status', 'Nenhuma sessão em andamento.');
        }

        return redirect()->route('sessions.show', $session);
    }

    public function show(Request $request, WorkoutSession $session): View
    {
        Gate::authorize('view', $session);

        $session->load('sessionExercises.exercise', 'sessionExercises.performedSets');
        $sessionExercises = $session->sessionExercises;
        $requestedPosition = (int) $request->integer('position', 1);
        $currentIndex = max(0, $sessionExercises->search(fn ($item): bool => $item->position === $requestedPosition));
        $currentExercise = $sessionExercises->get($currentIndex);

        abort_unless($currentExercise, 404);

        return view('sessions.show', [
            'session' => $session,
            'sessionExercises' => $sessionExercises,
            'currentExercise' => $currentExercise,
            'currentIndex' => $currentIndex,
            'previousExercise' => $sessionExercises->get($currentIndex - 1),
            'nextExercise' => $sessionExercises->get($currentIndex + 1),
            'plannedSetsCount' => $sessionExercises->sum('planned_sets'),
            'performedSetsCount' => $sessionExercises->sum(fn (SessionExercise $exercise): int => $exercise->performedSets->count()),
        ]);
    }

    public function finish(
        FinishWorkoutSessionRequest $request,
        WorkoutSession $session,
        FinishWorkoutSession $finishWorkoutSession,
    ): RedirectResponse {
        $finishWorkoutSession->execute($session);

        return redirect()->route('sessions.show', $session)
            ->with('status', 'Sessão finalizada. O registro agora é somente leitura.');
    }

    public function storeSet(
        StorePerformedSetRequest $request,
        WorkoutSession $session,
        SessionExercise $sessionExercise,
        RecordPerformedSet $recordPerformedSet,
    ): JsonResponse {
        $this->authorizeSetManagement($session, $sessionExercise);

        try {
            $performedSet = $recordPerformedSet->execute(
                $sessionExercise,
                $request->validated(),
            );
        } catch (IdempotencyConflictException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return response()->json([
            'message' => $performedSet->wasRecentlyCreated ? 'Série registrada.' : 'Série já registrada anteriormente.',
            'data' => $this->performedSetData($performedSet),
        ], $performedSet->wasRecentlyCreated ? 201 : 200);
    }

    public function updateSet(
        UpdatePerformedSetRequest $request,
        WorkoutSession $session,
        PerformedSet $performedSet,
        UpdatePerformedSet $updatePerformedSet,
    ): JsonResponse {
        $this->authorizeSetManagement($session, $performedSet->sessionExercise);

        $performedSet = $updatePerformedSet->execute($performedSet, $request->validated());

        return response()->json([
            'message' => 'Série atualizada.',
            'data' => $this->performedSetData($performedSet),
        ]);
    }

    public function destroySet(
        DeletePerformedSetRequest $request,
        WorkoutSession $session,
        PerformedSet $performedSet,
        DeletePerformedSet $deletePerformedSet,
    ): JsonResponse {
        $this->authorizeSetManagement($session, $performedSet->sessionExercise);
        $deletePerformedSet->execute($performedSet);

        return response()->json(['message' => 'Série excluída.']);
    }

    private function authorizeSetManagement(WorkoutSession $session, SessionExercise $sessionExercise): void
    {
        abort_unless($sessionExercise->workout_session_id === $session->id, 404);
        Gate::authorize('manageSets', $session);
    }

    /**
     * @return array<string, mixed>
     */
    private function performedSetData(PerformedSet $performedSet): array
    {
        return [
            'id' => $performedSet->id,
            'set_number' => $performedSet->set_number,
            'repetitions' => $performedSet->repetitions,
            'duration_seconds' => $performedSet->duration_seconds,
            'load_kg' => $performedSet->load_kg,
            'completed_at' => $performedSet->completed_at?->toIso8601String(),
        ];
    }
}
