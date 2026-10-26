<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Histórico de sessões</h2>
                <p class="mt-1 text-sm text-gray-600">Consulte seus treinos concluídos e o último desempenho por exercício.</p>
            </div>
            @if ($sessions->isNotEmpty())
                <a href="{{ route('sessions.current') }}" class="text-sm font-medium text-indigo-700 underline">Sessão atual</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            <section aria-labelledby="sessions-heading">
                <h3 id="sessions-heading" class="text-lg font-semibold text-gray-900">Sessões</h3>
                <div class="mt-4 space-y-4">
                    @forelse ($sessions as $session)
                        @php
                            $plannedSets = $session->sessionExercises->sum('planned_sets');
                            $performedSets = $session->sessionExercises->sum(fn ($exercise): int => $exercise->performedSets->count());
                        @endphp
                        <article class="rounded-lg bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $session->routine_name_snapshot }}</h4>
                                    <p class="mt-1 text-sm text-gray-600">Iniciada em {{ $session->started_at->format('d/m/Y H:i') }}</p>
                                    @if ($session->finished_at)
                                        <p class="mt-1 text-sm text-gray-600">Finalizada em {{ $session->finished_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </div>
                                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $session->status->value === 'completed' ? 'bg-gray-100 text-gray-700' : 'bg-emerald-100 text-emerald-800' }}">{{ $session->status->value === 'completed' ? 'Finalizada' : 'Em andamento' }}</span>
                            </div>
                            <dl class="mt-4 grid gap-3 text-sm text-gray-600 sm:grid-cols-3">
                                <div><dt class="font-medium text-gray-900">Exercícios</dt><dd>{{ $session->sessionExercises->count() }}</dd></div>
                                <div><dt class="font-medium text-gray-900">Séries registradas</dt><dd>{{ $performedSets }}</dd></div>
                                <div><dt class="font-medium text-gray-900">Séries planejadas</dt><dd>{{ $plannedSets }}</dd></div>
                            </dl>
                            <a href="{{ route('sessions.show', $session) }}" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Abrir sessão</a>
                        </article>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">Nenhuma sessão registrada ainda.</p>
                    @endforelse
                </div>
                @if ($sessions->hasPages())
                    <div class="mt-6">{{ $sessions->links() }}</div>
                @endif
            </section>

            <section aria-labelledby="latest-performance-heading">
                <h3 id="latest-performance-heading" class="text-lg font-semibold text-gray-900">Último desempenho por exercício</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @forelse ($latestPerformances as $performedSet)
                        @php($sessionExercise = $performedSet->sessionExercise)
                        <article class="rounded-lg bg-white p-6 shadow-sm">
                            <h4 class="font-semibold text-gray-900">{{ $sessionExercise->exercise?->name ?? $sessionExercise->exercise_name_snapshot }}</h4>
                            <p class="mt-1 text-sm text-gray-600">{{ $performedSet->completed_at->format('d/m/Y H:i') }}</p>
                            <p class="mt-4 text-sm text-gray-700">
                                @if ($performedSet->repetitions){{ $performedSet->repetitions }} repetições @endif
                                @if ($performedSet->duration_seconds) · {{ $performedSet->duration_seconds }} s @endif
                                @if ($performedSet->load_kg !== null) · {{ $performedSet->load_kg }} kg @endif
                            </p>
                            <a href="{{ route('sessions.show', $sessionExercise->workoutSession) }}" class="mt-4 inline-flex text-sm font-medium text-indigo-700 underline">Ver sessão</a>
                        </article>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">Finalize uma sessão com séries registradas para ver seu último desempenho.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
