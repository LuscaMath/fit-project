<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Escolha seu modo</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm" aria-labelledby="modalities-heading">
                <h3 id="modalities-heading" class="text-lg font-semibold text-gray-900">Qual modalidade você quer usar agora?</h3>
                <p class="mt-2 text-sm text-gray-600">A escolha filtra sua biblioteca de exercícios e pode ser trocada quando quiser.</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($modalities as $modality)
                        @php($label = $modality->value === 'gym' ? 'Academia' : 'Calistenia')
                        <form method="POST" action="{{ route('modalities.select', $modality->value) }}" class="rounded-lg border border-gray-200 p-5">
                            @csrf
                            <h4 class="text-base font-semibold text-gray-900">{{ $label }}</h4>
                            <p class="mt-2 text-sm text-gray-600">Ver exercícios e, nas próximas etapas, rotinas desta modalidade.</p>
                            <button type="submit" class="mt-5 inline-flex min-h-10 w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ $currentModality?->value === $modality->value ? 'Continuar em '.$label : 'Usar '.$label }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
