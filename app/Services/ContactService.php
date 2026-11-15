<?php

namespace App\Services;

use App\Models\Contact;
use App\Notifications\NewContactRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ContactService
{
    public function __construct(private SettingsService $settings) {}

    /**
     * Store a contact request and notify the configured recipients.
     *
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data): Contact
    {
        $contact = Contact::query()->create($data + ['privacy_accepted_at' => now()]);

        $this->notify($contact);

        return $contact;
    }

    /**
     * Delete (permanently) the requests older than the configured retention period (GDPR).
     */
    public function purgeExpired(): int
    {
        $limit = now()->subMonths($this->settings->retentionMonths());
        $count = 0;

        Contact::withTrashed()
            ->where('updated_at', '<', $limit)
            ->chunkById(100, function ($contacts) use (&$count): void {
                foreach ($contacts as $contact) {
                    $contact->forceDelete();
                    $count++;
                }
            });

        return $count;
    }

    /**
     * A mail failure must never lose the request: it is already stored and visible in the back office.
     */
    private function notify(Contact $contact): void
    {
        $recipients = $this->settings->notificationEmails();

        if ($recipients === []) {
            return;
        }

        try {
            Notification::route('mail', $recipients)->notify(new NewContactRequest($contact));
        } catch (Throwable $exception) {
            Log::error('Échec de la notification de nouvelle demande de contact.', [
                'contact_id' => $contact->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
