<?php

namespace App\Actions;

use App\Enums\WorkoutSessionStatus;
use App\Models\WorkoutSession;
use Illuminate\Support\Facades\DB;

class FinishWorkoutSession
{
    public function execute(WorkoutSession $session): WorkoutSession
    {
        return DB::transaction(function () use ($session): WorkoutSession {
            $session = WorkoutSession::query()
                ->whereKey($session->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status === WorkoutSessionStatus::Completed) {
                return $session;
            }

            $session->forceFill([
                'status' => WorkoutSessionStatus::Completed,
                'finished_at' => now(),
            ])->save();

            return $session->refresh();
        });
    }
}
