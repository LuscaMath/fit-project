<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $routine->name }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $routine->primary_modality->value === 'gym' ? 'Academia' : 'Calistenia' }} · {{ $routine->is_active ? 'Ativa' : 'Arquivada' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @php($activeSession = $routine->workoutSessions()->where('status', \App\Enums\WorkoutSessionStatus::InProgress->value)->first())
                @if ($routine->is_active)
                    @if ($activeSession)
                        <a href="{{ route('sessions.show', $activeSession) }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Retomar sessão</a>
                    @else
                        <form method="POST" action="{{ route('sessions.start', $routine) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Iniciar sessão</button>
                        </form>
                    @endif
                @endif
                <a href="{{ route('routines.edit', $routine) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Editar rotina</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($routine->description)
                <section class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Descrição</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $routine->description }}</p>
                </section>
            @endif

            <section class="rounded-lg bg-white p-5 shadow-sm" aria-labelledby="routine-plan-heading">
                <h3 id="routine-plan-heading" class="text-base font-semibold text-gray-900">Exercícios planejados</h3>
                <ol class="mt-4 space-y-3">
                    @foreach ($routine->routineExercises as $routineExercise)
                        <li class="rounded-md border border-gray-200 p-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <h4 class="font-semibold text-gray-900">{{ $routineExercise->position }}. {{ $routineExercise->exercise->name }}</h4>
                                <span class="text-sm text-gray-600">{{ $routineExercise->exercise->modality->value === 'gym' ? 'Academia' : 'Calistenia' }}</span>
                            </div>
                            <dl class="mt-3 grid gap-2 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-4">
                                <div><dt class="font-medium text-gray-900">Séries</dt><dd>{{ $routineExercise->planned_sets }}</dd></div>
                                @if ($routineExercise->target_reps)<div><dt class="font-medium text-gray-900">Repetições</dt><dd>{{ $routineExercise->target_reps }}</dd></div>@endif
                                @if ($routineExercise->target_duration_seconds)<div><dt class="font-medium text-gray-900">Duração</dt><dd>{{ $routineExercise->target_duration_seconds }} s</dd></div>@endif
                                @if ($routineExercise->target_load_kg !== null)<div><dt class="font-medium text-gray-900">Carga</dt><dd>{{ $routineExercise->target_load_kg }} kg</dd></div>@endif
                                @if ($routineExercise->rest_seconds !== null)<div><dt class="font-medium text-gray-900">Descanso</dt><dd>{{ $routineExercise->rest_seconds }} s</dd></div>@endif
                            </dl>
                            @if ($routineExercise->notes)<p class="mt-3 text-sm text-gray-600">{{ $routineExercise->notes }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>

            <a href="{{ route('routines.index') }}" class="inline-flex text-sm font-medium text-indigo-700 underline hover:text-indigo-900">Voltar para rotinas</a>
        </div>
    </div>
</x-app-layout>
