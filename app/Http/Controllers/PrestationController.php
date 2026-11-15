<?php

namespace App\Http\Controllers;

use App\Models\Prestation;
use App\Services\PublicContentService;
use Illuminate\Contracts\View\View;

class PrestationController extends Controller
{
    public function show(Prestation $prestation, PublicContentService $content): View
    {
        abort_unless($prestation->is_published, 404);

        return view('pages.prestation', [
            'prestation' => $prestation,
            'otherPrestations' => $content->prestations()->reject(fn (Prestation $other): bool => $other->is($prestation)),
        ]);
    }
}
