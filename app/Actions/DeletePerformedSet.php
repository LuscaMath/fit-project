<?php

namespace App\Actions;

use App\Models\PerformedSet;
use Illuminate\Support\Facades\DB;

class DeletePerformedSet
{
    public function execute(PerformedSet $performedSet): void
    {
        DB::transaction(fn (): bool => (bool) $performedSet->delete());
    }
}
