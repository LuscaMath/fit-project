<?php

namespace App\Enums;

enum MeasurementType: string
{
    case Repetitions = 'reps';
    case Duration = 'duration';
    case RepetitionsAndDuration = 'reps_and_duration';
}
