<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Novo exercício pessoal</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('exercises.store') }}" class="rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @include('exercises._form')
            </form>
        </div>
    </div>
</x-app-layout>
