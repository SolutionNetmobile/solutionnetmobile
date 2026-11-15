<?php

namespace App\Notifications;

use App\Filament\Resources\Contacts\ContactResource;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactRequest extends Notification
{
    use Queueable;

    public function __construct(public Contact $contact) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contact = $this->contact;

        return (new MailMessage)
            ->subject("Nouvelle demande de contact — {$contact->full_name}")
            ->replyTo($contact->email, $contact->full_name)
            ->greeting('Nouvelle demande de contact')
            ->line("**Nom :** {$contact->full_name}")
            ->line('**Entreprise :** '.($contact->company ?? '—'))
            ->line("**E-mail :** {$contact->email}")
            ->line('**Téléphone :** '.($contact->phone ?? '—'))
            ->line("**Type de projet :** {$contact->project_type->getLabel()}")
            ->line('**Budget estimatif :** '.($contact->budget?->getLabel() ?? 'Non précisé'))
            ->line('**Message :**')
            ->line($contact->message)
            ->action('Voir la demande', ContactResource::getUrl('view', ['record' => $contact]))
            ->line('Reçue le '.$contact->created_at->timezone(config('app.timezone'))->translatedFormat('d F Y à H:i').'.');
    }
}
