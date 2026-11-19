<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueCrmLabToken extends Command
{
    protected $signature = 'crm-lab:token {--read-only : Issue a token without write access}';

    protected $description = 'Issue a one-hour contact API token for the local CRM lab';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('Issue demo tokens only in the local lab.');

            return self::FAILURE;
        }

        $user = User::firstOrCreate(
            ['email' => 'crm-lab@example.test'],
            ['name' => 'CRM Lab', 'password' => Str::password(32)],
        );

        $abilities = $this->option('read-only')
            ? ['contacts:read']
            : ['contacts:read', 'contacts:write'];

        $token = $user->createToken('crm-lab', $abilities, now()->addHour());
        $this->output->writeln($token->plainTextToken);

        return self::SUCCESS;
    }
}
