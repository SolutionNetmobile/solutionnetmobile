<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Notifications\NewContactRequest;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        RateLimiter::clear('contact');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'last_name' => 'Dupont',
            'first_name' => 'Marie',
            'company' => 'ACME',
            'email' => 'Marie.Dupont@example.com',
            'phone' => '+33 6 12 34 56 78',
            'project_type' => 'website',
            'budget' => '5k_15k',
            'message' => 'Nous souhaitons refondre notre site vitrine pour 2027.',
            'privacy' => '1',
            'website' => '',
            'form_token' => Crypt::encryptString((string) now()->subMinute()->timestamp),
        ], $overrides);
    }

    public function test_the_form_is_rendered_with_csrf_and_anti_spam_fields(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="form_token"', false)
            ->assertSee('name="website"', false)
            ->assertSee('name="privacy"', false);
    }

    public function test_a_valid_request_is_stored_and_notified(): void
    {
        $this->post(route('contact.store'), $this->validPayload())
            ->assertRedirect(route('home').'#contact')
            ->assertSessionHas('contact_success')
            ->assertSessionHasNoErrors();

        $contact = Contact::query()->sole();

        $this->assertSame('Dupont', $contact->last_name);
        $this->assertSame('marie.dupont@example.com', $contact->email);
        $this->assertSame(ContactStatus::New, $contact->status);
        $this->assertNotNull($contact->created_at);
        $this->assertNotNull($contact->privacy_accepted_at);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewContactRequest::class,
            fn (NewContactRequest $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notification->contact->is($contact)
                && in_array('contact@solution-netmobile.fr', (array) $notifiable->routes['mail'], true),
        );
    }

    public function test_notifications_go_to_the_configured_recipients(): void
    {
        app(SettingsService::class)->set(['notification_emails' => 'a@example.com, b@example.com']);

        $this->post(route('contact.store'), $this->validPayload())->assertSessionHasNoErrors();

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewContactRequest::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === ['a@example.com', 'b@example.com'],
        );
    }

    public function test_required_fields_and_formats_are_validated(): void
    {
        $this->post(route('contact.store'), $this->validPayload([
            'last_name' => '',
            'first_name' => '',
            'email' => 'pas-un-email',
            'phone' => 'abc',
            'project_type' => 'inconnu',
            'budget' => 'illimité',
            'message' => 'Trop court',
            'privacy' => null,
        ]))->assertSessionHasErrors(['last_name', 'first_name', 'email', 'phone', 'project_type', 'budget', 'message', 'privacy']);

        $this->assertDatabaseCount('contacts', 0);
        Notification::assertNothingSent();
    }

    public function test_optional_fields_can_be_omitted(): void
    {
        $this->post(route('contact.store'), $this->validPayload([
            'company' => '',
            'phone' => '',
            'budget' => '',
        ]))->assertSessionHasNoErrors();

        $contact = Contact::query()->sole();
        $this->assertNull($contact->company);
        $this->assertNull($contact->phone);
        $this->assertNull($contact->budget);
    }

    public function test_html_is_stripped_from_text_fields(): void
    {
        $this->post(route('contact.store'), $this->validPayload(['last_name' => '<b>Dupont</b>']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Dupont', Contact::query()->sole()->last_name);
    }

    public function test_the_honeypot_blocks_bots(): void
    {
        $this->post(route('contact.store'), $this->validPayload(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_too_fast_or_forged_submissions_are_rejected(): void
    {
        $this->post(route('contact.store'), $this->validPayload(['form_token' => Crypt::encryptString((string) now()->timestamp)]))
            ->assertSessionHasErrors('form_token');

        $this->post(route('contact.store'), $this->validPayload(['form_token' => 'forged']))
            ->assertSessionHasErrors('form_token');

        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('contact.store'), $this->validPayload(['email' => "client{$i}@example.com"]))->assertRedirect();
        }

        $this->post(route('contact.store'), $this->validPayload())->assertTooManyRequests();
        $this->assertDatabaseCount('contacts', 3);
    }

    public function test_the_success_message_is_displayed_after_redirect(): void
    {
        $this->followingRedirects()
            ->post(route('contact.store'), $this->validPayload())
            ->assertOk()
            ->assertSee('Merci, votre demande a bien été envoyée.');
    }
}
