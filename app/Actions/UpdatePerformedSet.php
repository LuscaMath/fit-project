<?php

namespace App\Actions;

use App\Models\PerformedSet;
use Illuminate\Support\Facades\DB;

class UpdatePerformedSet
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(PerformedSet $performedSet, array $data): PerformedSet
    {
        return DB::transaction(function () use ($performedSet, $data): PerformedSet {
            $performedSet->update([
                'repetitions' => $data['repetitions'] ?? null,
                'duration_seconds' => $data['duration_seconds'] ?? null,
                'load_kg' => $data['load_kg'] ?? null,
            ]);

            return $performedSet->refresh();
        });
    }
}
