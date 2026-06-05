<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DavPasswordCommand extends Command
{
    protected $signature = 'dav:password
                            {email : User email (DAV login)}
                            {--password= : New password (prompted if omitted)}';

    protected $description = 'Change the DAV password for an existing user';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->components->error("No user found with email: {$email}");
            $this->line('Create one with: php artisan dav:setup --email='.$email);

            return self::FAILURE;
        }

        $password = $this->option('password') ?? $this->secret('New password');

        if ($password === null || $password === '') {
            $this->components->error('Password cannot be empty.');

            return self::FAILURE;
        }

        $user->password = $password;
        $user->save();

        $this->components->info('Password updated.');
        $this->components->twoColumnDetail('Login', $user->email);
        $this->components->twoColumnDetail('Principal', 'principals/'.($user->dav_username ?? strtok($user->email, '@')));

        return self::SUCCESS;
    }
}
