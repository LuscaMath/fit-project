<?php

namespace App\Policies;

use App\Enums\WorkoutSessionStatus;
use App\Models\User;
use App\Models\WorkoutSession;

class WorkoutSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkoutSession $session): bool
    {
        return $session->user_id === $user->id;
    }

    public function manageSets(User $user, WorkoutSession $session): bool
    {
        return $session->user_id === $user->id
            && $session->status === WorkoutSessionStatus::InProgress;
    }

    public function finish(User $user, WorkoutSession $session): bool
    {
        return $session->user_id === $user->id;
    }
}
