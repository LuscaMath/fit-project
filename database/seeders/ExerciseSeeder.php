<?php

namespace Database\Seeders;

use App\Enums\MeasurementType;
use App\Enums\Modality;
use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            [
                'name' => 'Supino reto com barra',
                'modality' => Modality::Gym,
                'measurement_type' => MeasurementType::Repetitions,
                'muscle_group' => 'peito',
                'equipment' => 'barra',
                'instructions' => 'Deite-se no banco, mantenha os pés apoiados e desça a barra de forma controlada até a linha do peito antes de empurrá-la para cima.',
                'video_url' => 'https://www.youtube.com/watch?v=IODxDxX7oi4',
            ],
            [
                'name' => 'Agachamento livre',
                'modality' => Modality::Gym,
                'measurement_type' => MeasurementType::Repetitions,
                'muscle_group' => 'pernas',
                'equipment' => 'barra',
                'instructions' => 'Mantenha o tronco firme, flexione quadril e joelhos e desça até uma amplitude confortável antes de retornar à posição inicial.',
                'video_url' => 'https://www.youtube.com/watch?v=Dy28eq2PjcM',
            ],
            [
                'name' => 'Barra fixa',
                'modality' => Modality::Calisthenics,
                'measurement_type' => MeasurementType::Repetitions,
                'muscle_group' => 'costas',
                'equipment' => 'barra fixa',
                'instructions' => 'Segure a barra, estabilize as escápulas e puxe o corpo até o queixo ultrapassar a barra, descendo com controle.',
                'video_url' => 'https://www.youtube.com/watch?v=eGo4IYlbE5g',
            ],
            [
                'name' => 'Flexão de braços',
                'modality' => Modality::Calisthenics,
                'measurement_type' => MeasurementType::Repetitions,
                'muscle_group' => 'peito',
                'equipment' => null,
                'instructions' => 'Mantenha o corpo alinhado, flexione os cotovelos próximos ao tronco e empurre o chão sem perder a postura.',
                'video_url' => null,
            ],
            [
                'name' => 'Prancha frontal',
                'modality' => Modality::Calisthenics,
                'measurement_type' => MeasurementType::Duration,
                'muscle_group' => 'abdômen',
                'equipment' => null,
                'instructions' => 'Apoie antebraços e pontas dos pés, contraia o abdômen e mantenha a coluna alinhada durante toda a permanência.',
                'video_url' => null,
            ],
        ];

        foreach ($exercises as $exercise) {
            Exercise::query()->updateOrCreate(
                ['user_id' => null, 'name' => $exercise['name']],
                $exercise,
            );
        }
    }
}
