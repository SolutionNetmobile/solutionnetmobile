<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams prospects as a CSV file (UTF-8 with BOM, ";" separator for Excel FR).
 */
class ContactExporter
{
    /**
     * @var array<int, string>
     */
    private const array HEADERS = [
        'ID', 'Date de réception', 'Nom', 'Prénom', 'Entreprise', 'E-mail', 'Téléphone',
        'Type de projet', 'Budget', 'Statut', 'Archivé', 'Message',
    ];

    /**
     * @param  Builder<Contact>  $query
     */
    public function download(Builder $query): StreamedResponse
    {
        $filename = 'prospects-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, self::HEADERS, ';', escape: '');

            $query->clone()->orderBy('created_at')->chunk(200, function ($contacts) use ($handle): void {
                foreach ($contacts as $contact) {
                    fputcsv($handle, $this->row($contact), ';', escape: '');
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<int, string|int|null>
     */
    public function row(Contact $contact): array
    {
        return array_map($this->neutralizeFormula(...), [
            $contact->id,
            $contact->created_at?->format('d/m/Y H:i'),
            $contact->last_name,
            $contact->first_name,
            $contact->company,
            $contact->email,
            $contact->phone,
            $contact->project_type?->getLabel(),
            $contact->budget?->getLabel(),
            $contact->status?->getLabel(),
            $contact->isArchived() ? 'Oui' : 'Non',
            $contact->message,
        ]);
    }

    /**
     * Prevent CSV/formula injection when the file is opened in a spreadsheet.
     */
    private function neutralizeFormula(string|int|null $value): string|int|null
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
