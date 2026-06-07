<?php

namespace App\Console\Commands;

use App\Dav\Services\DavUserProvisioner;
use Illuminate\Console\Command;

class DavSetupCommand extends Command
{
    protected $signature = 'dav:setup
                            {--email= : Login email for DAV clients}
                            {--name= : Display name}
                            {--username= : Principal name (default: admin)}
                            {--password= : Password (generated if omitted)}';

    protected $description = 'Create a DAV user with address book and calendar';

    public function handle(DavUserProvisioner $provisioner): int
    {
        $email = $this->option('email') ?? $this->ask('Email (used as login)', 'dav@carddav.test');
        $name = $this->option('name') ?? $this->ask('Display name', 'DAV User');
        $username = $this->option('username') ?? $this->ask('DAV username (principal)', config('dav.principal', 'admin'));
        $password = $this->option('password');

        $result = $provisioner->create(
            email: $email,
            name: $name,
            davUsername: $username,
            password: $password,
        );

        if (! $this->option('password')) {
            $this->components->info("Generated password: {$result['password']}");
        }

        $payload = $provisioner->toAccountPayload($result['user']);

        $this->newLine();
        $this->components->twoColumnDetail('Server URL', $payload['dav_url']);
        $this->components->twoColumnDetail('Username', $payload['email']);
        $this->components->twoColumnDetail('Password', $this->option('password') ? '(as provided)' : $result['password']);
        $this->components->twoColumnDetail('Principal', $payload['principal']);
        $this->newLine();
        $this->line('Configure iPhone/Android with the server URL above and these credentials.');
        $this->line('Use HTTPS in production (e.g. https://carddav.test/dav/).');

        return self::SUCCESS;
    }
}
