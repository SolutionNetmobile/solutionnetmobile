@props(['projectTypes' => [], 'budgets' => []])

@php
    $initialErrors = collect($errors->getMessages())->map(fn (array $messages): string => $messages[0])->all();
    $invalid = fn (string $name): string => $errors->has($name) ? 'true' : 'false';
    $describedBy = fn (string $name, bool $hint = false): string => trim(($hint ? "hint-{$name} " : '')."error-{$name}");
@endphp

<form
    method="POST"
    action="{{ route('contact.store') }}"
    novalidate
    x-data="contactForm(@js($initialErrors))"
    @submit="submit($event)"
    {{ $attributes->class('relative rounded-[var(--radius-card)] border border-brand-100 bg-white p-6 shadow-[var(--shadow-card)] sm:p-8') }}
>
    @csrf
    <input type="hidden" name="form_token" value="{{ \Illuminate\Support\Facades\Crypt::encryptString((string) now()->timestamp) }}">

    {{-- Honeypot: invisible to humans, filled by naive bots. --}}
    <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
        <label for="field-website">Ne pas remplir ce champ</label>
        <input type="text" id="field-website" name="website" value="" tabindex="-1" autocomplete="off">
    </div>

    @if (session('contact_success'))
        <div class="mb-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
            <x-site.icon name="circle-check" class="mt-0.5 size-5 shrink-0" />
            <p>{{ session('contact_success') }}</p>
        </div>
    @endif

    @error('form_token')
        <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            <x-site.icon name="circle-alert" class="mt-0.5 size-5 shrink-0" />
            <p>{{ $message }}</p>
        </div>
    @enderror

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.field name="last_name" label="Nom" required>
            <input id="field-last_name" name="last_name" type="text" class="form-input" autocomplete="family-name" required maxlength="100"
                value="{{ old('last_name') }}" aria-invalid="{{ $invalid('last_name') }}" aria-describedby="{{ $describedBy('last_name') }}" @blur="validateField($el)">
        </x-form.field>

        <x-form.field name="first_name" label="Prénom" required>
            <input id="field-first_name" name="first_name" type="text" class="form-input" autocomplete="given-name" required maxlength="100"
                value="{{ old('first_name') }}" aria-invalid="{{ $invalid('first_name') }}" aria-describedby="{{ $describedBy('first_name') }}" @blur="validateField($el)">
        </x-form.field>

        <x-form.field name="company" label="Entreprise">
            <input id="field-company" name="company" type="text" class="form-input" autocomplete="organization" maxlength="150"
                value="{{ old('company') }}" aria-invalid="{{ $invalid('company') }}" aria-describedby="{{ $describedBy('company') }}" @blur="validateField($el)">
        </x-form.field>

        <x-form.field name="email" label="Adresse e-mail" required>
            <input id="field-email" name="email" type="email" class="form-input" autocomplete="email" required maxlength="255" inputmode="email"
                value="{{ old('email') }}" aria-invalid="{{ $invalid('email') }}" aria-describedby="{{ $describedBy('email') }}" @blur="validateField($el)">
        </x-form.field>

        <x-form.field name="phone" label="Téléphone">
            <input id="field-phone" name="phone" type="tel" class="form-input" autocomplete="tel" inputmode="tel" maxlength="20"
                pattern="\+?[0-9\s\(\)\.\-]{8,20}" placeholder="+33 6 12 34 56 78"
                value="{{ old('phone') }}" aria-invalid="{{ $invalid('phone') }}" aria-describedby="{{ $describedBy('phone') }}" @blur="validateField($el)">
        </x-form.field>

        <x-form.field name="project_type" label="Type de projet" required>
            <select id="field-project_type" name="project_type" class="form-input" required
                aria-invalid="{{ $invalid('project_type') }}" aria-describedby="{{ $describedBy('project_type') }}" @change="validateField($el)">
                <option value="">Sélectionnez…</option>
                @foreach ($projectTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('project_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-form.field>

        <x-form.field name="budget" label="Budget estimatif" class="sm:col-span-2">
            <select id="field-budget" name="budget" class="form-input"
                aria-invalid="{{ $invalid('budget') }}" aria-describedby="{{ $describedBy('budget') }}">
                <option value="">Non précisé</option>
                @foreach ($budgets as $value => $label)
                    <option value="{{ $value }}" @selected(old('budget') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-form.field>

        <x-form.field name="message" label="Message" required hint="Décrivez votre projet, vos objectifs et vos délais (20 caractères minimum)." class="sm:col-span-2">
            <textarea id="field-message" name="message" rows="6" class="form-input resize-y" required minlength="20" maxlength="5000"
                aria-invalid="{{ $invalid('message') }}" aria-describedby="{{ $describedBy('message', true) }}" @blur="validateField($el)">{{ old('message') }}</textarea>
        </x-form.field>
    </div>

    <div class="mt-6">
        <div class="flex items-start gap-3">
            <input id="field-privacy" name="privacy" type="checkbox" value="1" required @checked(old('privacy'))
                class="mt-1 size-4.5 shrink-0 rounded border-slate-300 text-brand-600 accent-brand-600 focus:ring-brand-500"
                aria-invalid="{{ $invalid('privacy') }}" aria-describedby="error-privacy" @change="validateField($el)">
            <label for="field-privacy" class="text-sm leading-relaxed text-night/80">
                J’accepte que mes données soient utilisées pour traiter ma demande, conformément à la
                <a href="{{ route('pages.show', \App\Models\Page::PRIVACY_POLICY) }}" class="font-medium text-brand-600 underline underline-offset-2 hover:text-brand-700" target="_blank">politique de confidentialité</a>.
                <span class="text-brand-600" aria-hidden="true">*</span>
            </label>
        </div>
        <p id="error-privacy" class="form-error" role="alert" x-show="errors['privacy']" x-text="errors['privacy']" @unless ($errors->has('privacy')) x-cloak @endunless>
            {{ $errors->first('privacy') }}
        </p>
    </div>

    <div class="mt-7 flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-muted"><span class="text-brand-600">*</span> Champs obligatoires</p>
        <x-button type="submit" size="lg" class="w-full sm:w-auto" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
            <span x-show="!submitting">Envoyer ma demande</span>
            <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25" /><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
                Envoi en cours…
            </span>
        </x-button>
    </div>
</form>
