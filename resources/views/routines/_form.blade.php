@php
    $initialExercises = old('exercises');

    if ($initialExercises === null) {
        $initialExercises = $routine->exists
            ? $routine->routineExercises->map(fn ($item) => [
                'position' => $item->position,
                'exercise_id' => $item->exercise_id,
                'planned_sets' => $item->planned_sets,
                'target_reps' => $item->target_reps,
                'target_duration_seconds' => $item->target_duration_seconds,
                'target_load_kg' => $item->target_load_kg,
                'rest_seconds' => $item->rest_seconds,
                'notes' => $item->notes,
                'measurement_type' => $item->exercise->measurement_type->value,
            ])->values()->all()
            : [[
                'position' => 1,
                'exercise_id' => '',
                'planned_sets' => 3,
                'target_reps' => 10,
                'target_duration_seconds' => '',
                'target_load_kg' => '',
                'rest_seconds' => 90,
                'notes' => '',
                'measurement_type' => '',
            ]];
    }
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nome da rotina" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $routine->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="primary_modality" value="Modalidade principal" />
        <select id="primary_modality" name="primary_modality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($modalities as $modality)
                <option value="{{ $modality->value }}" @selected(old('primary_modality', $routine->primary_modality?->value) === $modality->value)>
                    {{ $modality->value === 'gym' ? 'Academia' : 'Calistenia' }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('primary_modality')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Descrição (opcional)" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $routine->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>

@if ($errors->any())
    <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
        <p class="font-semibold">Revise os dados da rotina:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="mt-8" x-data="{ rows: @js($initialExercises) }" aria-labelledby="routine-exercises-heading">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 id="routine-exercises-heading" class="text-base font-semibold text-gray-900">Exercícios e metas</h3>
            <p class="mt-1 text-sm text-gray-600">A ordem dos cartões define a ordem da rotina. Exercícios das duas modalidades podem ser misturados.</p>
        </div>
        <button type="button" @click="rows.push({ position: rows.length + 1, exercise_id: '', planned_sets: 3, target_reps: 10, target_duration_seconds: '', target_load_kg: '', rest_seconds: 90, notes: '', measurement_type: '' })" class="inline-flex min-h-10 items-center justify-center rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Adicionar exercício
        </button>
    </div>

    <x-input-error class="mt-2" :messages="$errors->get('exercises')" />

    <div class="mt-4 space-y-4">
        <template x-for="(row, index) in rows" :key="index">
            <article class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="font-semibold text-gray-900">Exercício <span x-text="index + 1"></span></h4>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="if (index > 0) { const item = rows.splice(index, 1)[0]; rows.splice(index - 1, 0, item); }" class="text-sm font-medium text-gray-700 hover:text-gray-900">Subir</button>
                        <button type="button" @click="if (index < rows.length - 1) { const item = rows.splice(index, 1)[0]; rows.splice(index + 1, 0, item); }" class="text-sm font-medium text-gray-700 hover:text-gray-900">Descer</button>
                        <button type="button" @click="rows.length > 1 ? rows.splice(index, 1) : null" class="text-sm font-medium text-red-700 hover:text-red-900">Remover</button>
                    </div>
                </div>

                <input type="hidden" :name="`exercises[${index}][position]`" :value="index + 1">
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700" :for="`exercise-${index}`">Exercício</label>
                        <select :id="`exercise-${index}`" :name="`exercises[${index}][exercise_id]`" x-model="row.exercise_id" @change="const type = $event.target.options[$event.target.selectedIndex].dataset.measurement; row.measurement_type = type; if (type === 'duration') { row.target_reps = ''; row.target_duration_seconds = row.target_duration_seconds || 60; } else if (type === 'reps') { row.target_duration_seconds = ''; row.target_reps = row.target_reps || 10; }" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Selecione um exercício</option>
                            @foreach ($exercises as $exercise)
                                <option value="{{ $exercise->id }}" data-measurement="{{ $exercise->measurement_type->value }}">{{ $exercise->name }} — {{ $exercise->modality->value === 'gym' ? 'Academia' : 'Calistenia' }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('exercises.*.exercise_id')" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" :for="`planned-sets-${index}`">Séries planejadas</label>
                        <input :id="`planned-sets-${index}`" :name="`exercises[${index}][planned_sets]`" x-model.number="row.planned_sets" type="number" min="1" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" :for="`rest-${index}`">Descanso (segundos)</label>
                        <input :id="`rest-${index}`" :name="`exercises[${index}][rest_seconds]`" x-model.number="row.rest_seconds" type="number" min="0" max="86400" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" :for="`reps-${index}`">Meta de repetições</label>
                        <input :id="`reps-${index}`" :name="`exercises[${index}][target_reps]`" x-model.number="row.target_reps" type="number" min="1" max="1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" :for="`duration-${index}`">Meta de duração (segundos)</label>
                        <input :id="`duration-${index}`" :name="`exercises[${index}][target_duration_seconds]`" x-model.number="row.target_duration_seconds" type="number" min="1" max="86400" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" :for="`load-${index}`">Carga (kg, opcional)</label>
                        <input :id="`load-${index}`" :name="`exercises[${index}][target_load_kg]`" x-model="row.target_load_kg" type="number" min="0" step="0.01" max="999999.99" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-sm font-medium text-gray-700" :for="`notes-${index}`">Observações (opcional)</label>
                        <textarea :id="`notes-${index}`" :name="`exercises[${index}][notes]`" x-model="row.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
            </article>
        </template>
    </div>
</section>

<div class="mt-8 flex items-center gap-4">
    <x-primary-button>{{ $routine->exists ? 'Salvar alterações' : 'Criar rotina' }}</x-primary-button>
    <a href="{{ $routine->exists ? route('routines.show', $routine) : route('routines.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">Cancelar</a>
</div>

