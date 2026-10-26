@php($isInProgress = $session->status === \App\Enums\WorkoutSessionStatus::InProgress)

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $session->routine_name_snapshot }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $session->primary_modality_snapshot->value === 'gym' ? 'Academia' : 'Calistenia' }} · Exercício {{ $currentIndex + 1 }} de {{ $sessionExercises->count() }}</p>
            </div>
            <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $isInProgress ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $isInProgress ? 'Sessão em andamento' : 'Sessão finalizada' }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="rounded-lg bg-white p-6 shadow-sm" aria-labelledby="session-summary-heading">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 id="session-summary-heading" class="text-lg font-semibold text-gray-900">Resumo da sessão</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $performedSetsCount }} de {{ $plannedSetsCount }} séries planejadas registradas.</p>
                        @if ($session->finished_at)
                            <p class="mt-1 text-sm text-gray-600">Finalizada em {{ $session->finished_at->format('d/m/Y H:i') }}.</p>
                        @endif
                    </div>
                    @if ($isInProgress)
                        <form method="POST" action="{{ route('sessions.finish', $session) }}" onsubmit="return confirm('Finalizar esta sessão agora? Exercícios pendentes serão mantidos no resumo.');">
                            @csrf
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Finalizar sessão</button>
                        </form>
                    @else
                        <span class="text-sm font-medium text-gray-600">Somente leitura</span>
                    @endif
                </div>
            </section>

            <section
                class="rounded-lg bg-white p-6 shadow-sm"
                aria-labelledby="current-exercise-heading"
                x-data="performedSetForm(@js([
                    'measurementType' => $currentExercise->measurement_type_snapshot->value,
                    'sets' => $currentExercise->performedSets->map(fn ($set) => [
                        'id' => $set->id,
                        'set_number' => $set->set_number,
                        'repetitions' => $set->repetitions,
                        'duration_seconds' => $set->duration_seconds,
                        'load_kg' => $set->load_kg,
                        'completed_at' => $set->completed_at?->toIso8601String(),
                        'editing' => false,
                    ])->values(),
                    'nextSetNumber' => ($currentExercise->performedSets->max('set_number') ?? 0) + 1,
                    'storeUrl' => route('sessions.sets.store', [$session, $currentExercise]),
                    'updateUrlTemplate' => route('sessions.sets.update', [$session, '__SET__']),
                    'destroyUrlTemplate' => route('sessions.sets.destroy', [$session, '__SET__']),
                ]))"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-700">Exercício {{ $currentExercise->position }}</p>
                        <h3 id="current-exercise-heading" class="mt-1 text-2xl font-semibold text-gray-900">{{ $currentExercise->exercise_name_snapshot }}</h3>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">{{ $currentExercise->measurement_type_snapshot->value }}</span>
                </div>

                @if ($currentExercise->exercise)
                    <dl class="mt-6 grid gap-3 text-sm text-gray-600 sm:grid-cols-2">
                        <div><dt class="font-medium text-gray-900">Grupo muscular</dt><dd>{{ $currentExercise->exercise->muscle_group }}</dd></div>
                        @if ($currentExercise->exercise->equipment)<div><dt class="font-medium text-gray-900">Equipamento</dt><dd>{{ $currentExercise->exercise->equipment }}</dd></div>@endif
                    </dl>
                    <div class="mt-6 rounded-md bg-gray-50 p-4">
                        <h4 class="text-sm font-semibold text-gray-900">Orientações</h4>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $currentExercise->exercise->instructions }}</p>
                        @if ($currentExercise->exercise->videoEmbedUrl())
                            <div class="mt-4 aspect-video overflow-hidden rounded-md">
                                <iframe class="h-full w-full" src="{{ $currentExercise->exercise->videoEmbedUrl() }}" title="Vídeo de orientação" allowfullscreen></iframe>
                            </div>
                        @elseif ($currentExercise->exercise->video_url)
                            <a href="{{ $currentExercise->exercise->video_url }}" target="_blank" rel="noreferrer" class="mt-3 inline-flex text-sm font-medium text-indigo-700 underline">Abrir vídeo de orientação</a>
                        @endif
                    </div>
                @else
                    <p class="mt-6 text-sm text-gray-600">As orientações deste exercício não estão mais disponíveis, mas o snapshot da sessão foi preservado.</p>
                @endif

                <dl class="mt-6 grid gap-3 rounded-md border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-950 sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="font-medium">Séries planejadas</dt><dd>{{ $currentExercise->planned_sets }}</dd></div>
                    @if ($currentExercise->target_reps)<div><dt class="font-medium">Repetições</dt><dd>{{ $currentExercise->target_reps }}</dd></div>@endif
                    @if ($currentExercise->target_duration_seconds)<div><dt class="font-medium">Duração</dt><dd>{{ $currentExercise->target_duration_seconds }} s</dd></div>@endif
                    @if ($currentExercise->target_load_kg !== null)<div><dt class="font-medium">Carga</dt><dd>{{ $currentExercise->target_load_kg }} kg</dd></div>@endif
                    @if ($currentExercise->rest_seconds !== null)<div><dt class="font-medium">Descanso</dt><dd>{{ $currentExercise->rest_seconds }} s</dd></div>@endif
                </dl>

                <section class="mt-8 border-t border-gray-200 pt-6" aria-labelledby="performed-sets-heading">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h4 id="performed-sets-heading" class="text-lg font-semibold text-gray-900">Séries executadas</h4>
                            <p class="mt-1 text-sm text-gray-600">{{ $isInProgress ? 'Registre cada série individualmente. Salvar não avança automaticamente.' : 'As séries registradas nesta sessão são exibidas somente para consulta.' }}</p>
                        </div>
                    </div>

                    @if ($isInProgress)
                        <div x-show="error" x-text="error" role="alert" class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-800"></div>
                        <div x-show="status" x-text="status" role="status" class="mt-4 rounded-md bg-emerald-50 p-3 text-sm text-emerald-800"></div>

                        <form class="mt-4 grid gap-4 rounded-md bg-gray-50 p-4 sm:grid-cols-2 lg:grid-cols-4" @submit.prevent="submit">
                        <div>
                            <label for="set-number" class="block text-sm font-medium text-gray-700">Número da série</label>
                            <input id="set-number" x-model.number="form.set_number" type="number" min="1" max="65535" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        @if (in_array($currentExercise->measurement_type_snapshot->value, ['reps', 'reps_and_duration'], true))
                            <div>
                                <label for="performed-repetitions" class="block text-sm font-medium text-gray-700">Repetições</label>
                                <input id="performed-repetitions" x-model.number="form.repetitions" type="number" min="1" max="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @if ($currentExercise->measurement_type_snapshot->value === 'reps') required @endif>
                            </div>
                        @endif

                        @if (in_array($currentExercise->measurement_type_snapshot->value, ['duration', 'reps_and_duration'], true))
                            <div>
                                <label for="performed-duration" class="block text-sm font-medium text-gray-700">Duração (segundos)</label>
                                <input id="performed-duration" x-model.number="form.duration_seconds" type="number" min="1" max="86400" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @if ($currentExercise->measurement_type_snapshot->value === 'duration') required @endif>
                            </div>
                        @endif

                        <div>
                            <label for="performed-load" class="block text-sm font-medium text-gray-700">Carga (kg)</label>
                            <input id="performed-load" x-model="form.load_kg" type="number" min="0" step="0.01" max="999999.99" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="sm:col-span-2 lg:col-span-4">
                            <button type="submit" :disabled="saving" class="inline-flex min-h-10 items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                                <span x-text="saving ? 'Salvando...' : 'Registrar série'"></span>
                            </button>
                        </div>
                        </form>
                    @endif

                    <div class="mt-4 space-y-3">
                        <p x-show="sets.length === 0" class="rounded-md border border-dashed border-gray-300 p-4 text-sm text-gray-600">Nenhuma série registrada para este exercício.</p>
                        <template x-for="set in sets" :key="set.id">
                            <article class="rounded-md border border-gray-200 p-4">
                                <div x-show="!set.editing" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="text-sm text-gray-700">
                                        <span class="font-semibold">Série <span x-text="set.set_number"></span></span>
                                        <span class="mx-2 text-gray-400">·</span>
                                        <span x-show="set.repetitions !== null && set.repetitions !== ''"><span x-text="set.repetitions"></span> repetições</span>
                                        <span x-show="set.duration_seconds !== null && set.duration_seconds !== ''"><span x-text="set.duration_seconds"></span> s</span>
                                        <span x-show="set.load_kg !== null && set.load_kg !== ''"> · <span x-text="set.load_kg"></span> kg</span>
                                    </div>
                                    @if ($isInProgress)
                                        <div class="flex gap-3">
                                            <button type="button" @click="beginEdit(set)" class="text-sm font-medium text-indigo-700 underline">Editar</button>
                                            <button type="button" @click="remove(set)" :disabled="busySetId === set.id" class="text-sm font-medium text-red-700 underline disabled:opacity-60">Excluir</button>
                                        </div>
                                    @endif
                                </div>

                                @if ($isInProgress)
                                    <div x-show="set.editing" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-700">Número da série</span>
                                        <span class="mt-2 block text-sm text-gray-600" x-text="set.set_number"></span>
                                    </div>
                                    @if (in_array($currentExercise->measurement_type_snapshot->value, ['reps', 'reps_and_duration'], true))
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Repetições</label>
                                            <input x-model.number="set.repetitions" type="number" min="1" max="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    @endif
                                    @if (in_array($currentExercise->measurement_type_snapshot->value, ['duration', 'reps_and_duration'], true))
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Duração (segundos)</label>
                                            <input x-model.number="set.duration_seconds" type="number" min="1" max="86400" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Carga (kg)</label>
                                        <input x-model="set.load_kg" type="number" min="0" step="0.01" max="999999.99" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="flex items-end gap-3 sm:col-span-2 lg:col-span-4">
                                        <button type="button" @click="update(set)" :disabled="busySetId === set.id" class="inline-flex min-h-10 items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">Salvar</button>
                                        <button type="button" @click="cancelEdit(set)" :disabled="busySetId === set.id" class="text-sm font-medium text-gray-700 underline disabled:opacity-60">Cancelar</button>
                                    </div>
                                    </div>
                                @endif
                            </article>
                        </template>
                    </div>
                </section>
            </section>

            <div class="flex items-center justify-between gap-3">
                @if ($previousExercise)
                    <a href="{{ route('sessions.show', ['session' => $session, 'position' => $previousExercise->position]) }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Anterior</a>
                @else
                    <span></span>
                @endif
                @if ($nextExercise)
                    <a href="{{ route('sessions.show', ['session' => $session, 'position' => $nextExercise->position]) }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Próximo</a>
                @endif
            </div>

            @if ($session->workout_routine_id)
                <a href="{{ route('routines.show', $session->workout_routine_id) }}" class="inline-flex text-sm font-medium text-indigo-700 underline hover:text-indigo-900">Voltar para a rotina</a>
            @else
                <a href="{{ route('routines.index') }}" class="inline-flex text-sm font-medium text-indigo-700 underline hover:text-indigo-900">Voltar para rotinas</a>
            @endif
        </div>
    </div>
</x-app-layout>
