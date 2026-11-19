<?php

namespace App\Console\Commands;

use App\Jobs\ExportContacts;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ExportCrmLabContacts extends Command
{
    protected $signature = 'crm-lab:export {--queue=exports : Queue to use for the export}';

    protected $description = 'Queue a contact export for the local CRM lab user';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Run the demo export only in the local lab.');

            return self::FAILURE;
        }

        $user = User::where('email', 'crm-lab@example.test')->first();
        if ($user === null) {
            $this->error('Run php artisan crm-lab:token to create the demo user.');

            return self::FAILURE;
        }

        $exportId = (string) Str::uuid();
        ExportContacts::dispatch($user->getKey(), $exportId)->onQueue($this->option('queue'));
        $this->line("Queued export: contact-exports/{$user->getKey()}/{$exportId}.json");

        return self::SUCCESS;
    }
}
