<?php

namespace App\Enums;

enum WorkoutSessionStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
