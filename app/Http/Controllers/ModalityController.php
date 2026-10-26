<?php

namespace App\Http\Controllers;

use App\Enums\Modality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModalityController extends Controller
{
    public function index(Request $request): View
    {
        $currentModality = Modality::tryFrom($request->session()->get('current_modality'));

        return view('modalities.index', [
            'modalities' => Modality::cases(),
            'currentModality' => $currentModality,
        ]);
    }

    public function select(Request $request, string $modality): RedirectResponse
    {
        $selectedModality = Modality::tryFrom($modality);

        abort_unless($selectedModality, 404);

        $request->session()->put('current_modality', $selectedModality->value);

        return redirect()->route('modalities.exercises', $selectedModality->value);
    }
}
