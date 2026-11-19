<?php

namespace App\Jobs;

use App\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ExportContacts implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 20;

    public function __construct(public int $userId, public string $exportId) {}

    public function handle(): void
    {
        $contacts = Contact::query()
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        $saved = Storage::disk('local')->put(
            "contact-exports/{$this->userId}/{$this->exportId}.json",
            $contacts->toJson(JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL,
        );

        if (! $saved) {
            throw new RuntimeException('The contact export could not be written.');
        }
    }
}
