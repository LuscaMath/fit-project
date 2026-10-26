<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Biblioteca de exercícios</h2>
            <a href="{{ route('exercises.create') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Novo exercício pessoal
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            <section class="rounded-lg bg-white p-5 shadow-sm" aria-labelledby="filters-heading">
                <h3 id="filters-heading" class="text-base font-semibold text-gray-900">Filtrar biblioteca</h3>
                <form method="GET" action="{{ route($libraryRoute, $libraryRouteParameters) }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                    <div>
                        <label for="modality" class="block text-sm font-medium text-gray-700">Modalidade</label>
                        <select id="modality" name="modality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @if (! $selectedModality)
                                <option value="">Todas</option>
                            @endif
                            @foreach ($modalities as $modality)
                                <option value="{{ $modality->value }}" @selected(($filters['modality'] ?? '') === $modality->value)>
                                    {{ $modality->value === 'gym' ? 'Academia' : 'Calistenia' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="muscle_group" class="block text-sm font-medium text-gray-700">Grupo muscular</label>
                        <select id="muscle_group" name="muscle_group" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            @foreach ($muscleGroups as $muscleGroup)
                                <option value="{{ $muscleGroup }}" @selected(($filters['muscle_group'] ?? '') === $muscleGroup)>{{ $muscleGroup }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="equipment" class="block text-sm font-medium text-gray-700">Equipamento</label>
                        <select id="equipment" name="equipment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            @foreach ($equipmentOptions as $equipment)
                                <option value="{{ $equipment }}" @selected(($filters['equipment'] ?? '') === $equipment)>{{ $equipment }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Aplicar filtros</button>
                </form>
            </section>

            @if ($exercises->isEmpty())
                <div class="rounded-lg bg-white p-8 text-center text-gray-600 shadow-sm">Nenhum exercício encontrado para esses filtros.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($exercises as $exercise)
                        <article class="flex h-full flex-col rounded-lg bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $exercise->name }}</h3>
                                <span class="shrink-0 rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                    {{ $exercise->user_id === null ? 'Global' : 'Pessoal' }}
                                </span>
                            </div>
                            <dl class="mt-4 space-y-2 text-sm text-gray-600">
                                <div class="flex justify-between gap-4"><dt>Modalidade</dt><dd class="font-medium text-gray-900">{{ $exercise->modality->value === 'gym' ? 'Academia' : 'Calistenia' }}</dd></div>
                                <div class="flex justify-between gap-4"><dt>Grupo</dt><dd class="font-medium text-gray-900">{{ $exercise->muscle_group }}</dd></div>
                                <div class="flex justify-between gap-4"><dt>Métrica</dt><dd class="font-medium text-gray-900">{{ str_replace('_', ' + ', $exercise->measurement_type->value) }}</dd></div>
                                @if ($exercise->equipment)
                                    <div class="flex justify-between gap-4"><dt>Equipamento</dt><dd class="font-medium text-gray-900">{{ $exercise->equipment }}</dd></div>
                                @endif
                            </dl>
                            <a href="{{ route('exercises.show', $exercise) }}" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Ver orientações</a>
                        </article>
                    @endforeach
                </div>

                <div>{{ $exercises->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
