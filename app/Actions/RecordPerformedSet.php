<?php

namespace App\Actions;

use App\Exceptions\IdempotencyConflictException;
use App\Models\PerformedSet;
use App\Models\SessionExercise;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPerformedSet
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(SessionExercise $sessionExercise, array $data): PerformedSet
    {
        return DB::transaction(function () use ($sessionExercise, $data): PerformedSet {
            $existing = PerformedSet::query()
                ->where('client_request_id', $data['client_request_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $this->ensureSamePayload($existing, $sessionExercise, $data);

                return $existing;
            }

            try {
                return $sessionExercise->performedSets()->create([
                    'set_number' => $data['set_number'],
                    'repetitions' => $data['repetitions'] ?? null,
                    'duration_seconds' => $data['duration_seconds'] ?? null,
                    'load_kg' => $data['load_kg'] ?? null,
                    'client_request_id' => $data['client_request_id'],
                    'completed_at' => now(),
                ]);
            } catch (QueryException $exception) {
                $existing = PerformedSet::query()
                    ->where('client_request_id', $data['client_request_id'])
                    ->first();

                if ($existing) {
                    $this->ensureSamePayload($existing, $sessionExercise, $data);

                    return $existing;
                }

                if ($sessionExercise->performedSets()->where('set_number', $data['set_number'])->exists()) {
                    throw ValidationException::withMessages([
                        'set_number' => 'Este número de série já foi registrado para o exercício.',
                    ]);
                }

                throw $exception;
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureSamePayload(PerformedSet $existing, SessionExercise $sessionExercise, array $data): void
    {
        $samePayload = $existing->session_exercise_id === (int) $sessionExercise->id
            && $existing->set_number === (int) $data['set_number']
            && $existing->repetitions === ($data['repetitions'] ?? null)
            && $existing->duration_seconds === ($data['duration_seconds'] ?? null)
            && $this->sameLoad($existing->load_kg, $data['load_kg'] ?? null);

        if (! $samePayload) {
            throw new IdempotencyConflictException;
        }
    }

    private function sameLoad(mixed $existing, mixed $incoming): bool
    {
        if ($existing === null || $incoming === null) {
            return $existing === $incoming;
        }

        return number_format((float) $existing, 2, '.', '') === number_format((float) $incoming, 2, '.', '');
    }
}
