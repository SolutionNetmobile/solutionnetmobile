<?php

namespace App\Http\Controllers;

use App\Enums\Budget;
use App\Enums\ProjectType;
use App\Services\PublicContentService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(PublicContentService $content): View
    {
        return view('pages.home', [
            'prestations' => $content->prestations(),
            'methodologies' => $content->methodologies(),
            'clients' => $content->clients(),
            'projectTypes' => ProjectType::options(),
            'budgets' => Budget::options(),
        ]);
    }
}
