<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Rotinas{{ $selectedModality ? ' · '.($selectedModality->value === 'gym' ? 'Academia' : 'Calistenia') : '' }}</h2>
                <p class="mt-1 text-sm text-gray-600">Organize a ordem e as metas dos seus exercícios.</p>
            </div>
            <a href="{{ route('routines.create', $selectedModality ? ['primary_modality' => $selectedModality->value] : []) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Nova rotina</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            <form method="GET" action="{{ route($libraryRoute, $libraryRouteParameters) }}" class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                <input id="include_archived" name="include_archived" value="1" type="checkbox" @checked($includeArchived) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="include_archived" class="text-sm font-medium text-gray-700">Incluir rotinas arquivadas</label>
                <button type="submit" class="ml-auto inline-flex min-h-10 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Aplicar</button>
            </form>

            @if ($routines->isEmpty())
                <div class="rounded-lg bg-white p-8 text-center text-gray-600 shadow-sm">Nenhuma rotina encontrada.</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($routines as $routine)
                        <article class="flex h-full flex-col rounded-lg bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $routine->name }}</h3>
                                <span class="shrink-0 rounded-full px-2 py-1 text-xs font-medium {{ $routine->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $routine->is_active ? 'Ativa' : 'Arquivada' }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">{{ $routine->primary_modality->value === 'gym' ? 'Academia' : 'Calistenia' }} · {{ $routine->routine_exercises_count }} {{ $routine->routine_exercises_count === 1 ? 'exercício' : 'exercícios' }}</p>
                            @if ($routine->description)
                                <p class="mt-4 line-clamp-3 text-sm text-gray-600">{{ $routine->description }}</p>
                            @endif
                            <div class="mt-auto flex flex-wrap gap-2 pt-5">
                                <a href="{{ route('routines.show', $routine) }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Ver rotina</a>
                                <a href="{{ route('routines.edit', $routine) }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
                                <form method="POST" action="{{ route('routines.archive', $routine) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">{{ $routine->is_active ? 'Arquivar' : 'Restaurar' }}</button>
                                </form>
                                @if (! $routine->is_active)
                                    <form method="POST" action="{{ route('routines.destroy', $routine) }}" onsubmit="return confirm('Excluir esta rotina definitivamente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Excluir</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div>{{ $routines->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
