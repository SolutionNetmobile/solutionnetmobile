<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Services\ContactService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request, ContactService $contacts, SettingsService $settings): RedirectResponse
    {
        $contacts->submit($request->contactData());

        return redirect()
            ->to(route('home').'#contact')
            ->with('contact_success', $settings->get('contact_success_message'));
    }
}
