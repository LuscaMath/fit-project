<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $exercise->name }}</h2>
            <a href="{{ route('exercises.index') }}" class="text-sm font-medium text-indigo-700 underline">Voltar para a biblioteca</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-5xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <article class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                @if (session('status'))
                    <div role="status" class="mb-6 rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $exercise->modality->value === 'gym' ? 'Academia' : 'Calistenia' }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ $exercise->user_id === null ? 'Catálogo global' : 'Exercício pessoal' }}</span>
                </div>

                <h3 class="mt-5 text-lg font-semibold text-gray-900">Instruções</h3>
                <p class="mt-2 whitespace-pre-line leading-7 text-gray-700">{{ $exercise->instructions }}</p>

                <dl class="mt-6 grid gap-4 border-t border-gray-100 pt-6 sm:grid-cols-3">
                    <div><dt class="text-sm text-gray-500">Grupo muscular</dt><dd class="mt-1 font-medium text-gray-900">{{ $exercise->muscle_group }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Métrica</dt><dd class="mt-1 font-medium text-gray-900">{{ str_replace('_', ' + ', $exercise->measurement_type->value) }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Equipamento</dt><dd class="mt-1 font-medium text-gray-900">{{ $exercise->equipment ?: 'Nenhum' }}</dd></div>
                </dl>

                @if ($exercise->videoEmbedUrl())
                    <h3 class="mt-8 text-lg font-semibold text-gray-900">Vídeo</h3>
                    <div class="mt-3 aspect-video overflow-hidden rounded-lg bg-gray-100">
                        <iframe class="h-full w-full" src="{{ $exercise->videoEmbedUrl() }}" title="Vídeo de {{ $exercise->name }}" loading="lazy" allowfullscreen></iframe>
                    </div>
                @elseif ($exercise->video_url)
                    <h3 class="mt-8 text-lg font-semibold text-gray-900">Vídeo</h3>
                    <a class="mt-3 inline-flex text-indigo-700 underline" href="{{ $exercise->video_url }}" target="_blank" rel="noopener noreferrer">Abrir vídeo em uma nova aba</a>
                @endif
            </article>

            <aside class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Ações</h3>
                <div class="mt-4 space-y-3">
                    @can('update', $exercise)
                        <a href="{{ route('exercises.edit', $exercise) }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Editar exercício</a>
                    @endcan
                    <a href="{{ route('exercises.index') }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Ver biblioteca</a>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
