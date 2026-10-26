<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nome" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $exercise->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="modality" value="Modalidade" />
        <select id="modality" name="modality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($modalities as $modality)
                <option value="{{ $modality->value }}" @selected(old('modality', $exercise->modality?->value) === $modality->value)>
                    {{ $modality->value === 'gym' ? 'Academia' : 'Calistenia' }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('modality')" />
    </div>

    <div>
        <x-input-label for="measurement_type" value="Tipo de medição" />
        <select id="measurement_type" name="measurement_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($measurementTypes as $measurementType)
                <option value="{{ $measurementType->value }}" @selected(old('measurement_type', $exercise->measurement_type?->value) === $measurementType->value)>
                    {{ str_replace('_', ' + ', $measurementType->value) }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('measurement_type')" />
    </div>

    <div>
        <x-input-label for="muscle_group" value="Grupo muscular" />
        <x-text-input id="muscle_group" name="muscle_group" type="text" class="mt-1 block w-full" :value="old('muscle_group', $exercise->muscle_group)" required />
        <x-input-error class="mt-2" :messages="$errors->get('muscle_group')" />
    </div>

    <div>
        <x-input-label for="equipment" value="Equipamento (opcional)" />
        <x-text-input id="equipment" name="equipment" type="text" class="mt-1 block w-full" :value="old('equipment', $exercise->equipment)" />
        <x-input-error class="mt-2" :messages="$errors->get('equipment')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="instructions" value="Instruções" />
        <textarea id="instructions" name="instructions" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('instructions', $exercise->instructions) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('instructions')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="video_url" value="URL do vídeo (YouTube ou Vimeo, opcional)" />
        <x-text-input id="video_url" name="video_url" type="url" class="mt-1 block w-full" :value="old('video_url', $exercise->video_url)" placeholder="https://www.youtube.com/watch?v=..." />
        <x-input-error class="mt-2" :messages="$errors->get('video_url')" />
    </div>
</div>

<div class="mt-6 flex items-center gap-4">
    <x-primary-button>{{ $exercise->exists ? 'Salvar alterações' : 'Criar exercício' }}</x-primary-button>
    <a href="{{ $exercise->exists ? route('exercises.show', $exercise) : route('exercises.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">Cancelar</a>
</div>
