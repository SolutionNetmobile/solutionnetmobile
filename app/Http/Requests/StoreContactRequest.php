<?php

namespace App\Http\Requests;

use App\Enums\Budget;
use App\Enums\ProjectType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreContactRequest extends FormRequest
{
    /**
     * Minimum time (seconds) a human needs to fill the form; faster submissions are treated as bots.
     */
    public const int MIN_FILL_SECONDS = 3;

    /**
     * Phone format: digits, spaces, dots, dashes, parentheses and an optional leading "+".
     */
    public const string PHONE_PATTERN = '/^\+?[0-9\s().-]{8,20}$/';

    /**
     * Where to send the visitor back (with errors) after a failed validation.
     */
    protected $redirect = '/#contact';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->clean('first_name'),
            'last_name' => $this->clean('last_name'),
            'company' => $this->clean('company'),
            'email' => mb_strtolower((string) $this->clean('email')),
            'phone' => $this->clean('phone'),
            'message' => is_string($this->input('message')) ? trim($this->input('message')) : $this->input('message'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'email:rfc,strict', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:'.self::PHONE_PATTERN],
            'project_type' => ['required', Rule::enum(ProjectType::class)],
            'budget' => ['nullable', Rule::enum(Budget::class)],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'privacy' => ['accepted'],
            // Anti-spam: honeypot field that must stay empty + signed form timestamp.
            'website' => ['nullable', 'prohibited'],
            'form_token' => ['required', 'string'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('form_token')) {
                    return;
                }

                try {
                    $renderedAt = (int) Crypt::decryptString((string) $this->input('form_token'));
                } catch (DecryptException) {
                    $validator->errors()->add('form_token', 'Le formulaire a expiré, merci de réessayer.');

                    return;
                }

                if (now()->timestamp - $renderedAt < self::MIN_FILL_SECONDS) {
                    $validator->errors()->add('form_token', 'Votre envoi a été bloqué par notre protection anti-spam. Merci de réessayer.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Veuillez saisir un numéro de téléphone valide (ex. : +33 6 12 34 56 78).',
            'privacy.accepted' => 'Vous devez accepter la politique de confidentialité pour envoyer votre demande.',
            'website.prohibited' => 'Votre envoi a été bloqué par notre protection anti-spam.',
            'form_token.required' => 'Le formulaire a expiré, merci de réessayer.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'last_name' => 'nom',
            'first_name' => 'prénom',
            'company' => 'entreprise',
            'email' => 'adresse e-mail',
            'phone' => 'téléphone',
            'project_type' => 'type de projet',
            'budget' => 'budget estimatif',
            'message' => 'message',
        ];
    }

    /**
     * Only the fields that are persisted (data minimisation).
     *
     * @return array<string, mixed>
     */
    public function contactData(): array
    {
        return $this->safe()->only([
            'first_name', 'last_name', 'company', 'email', 'phone', 'project_type', 'budget', 'message',
        ]);
    }

    private function clean(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $value = trim(strip_tags($value));

        return $value === '' ? null : $value;
    }
}
