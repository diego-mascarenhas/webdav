<?php

namespace App\Console\Commands;

use App\Dav\Services\DavPrincipalService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DavSetupCommand extends Command
{
    protected $signature = 'dav:setup
                            {--email= : Login email for DAV clients}
                            {--name= : Display name}
                            {--username= : Principal name (default: admin)}
                            {--password= : Password (generated if omitted)}';

    protected $description = 'Create a DAV user with address book and calendar';

    public function handle(DavPrincipalService $davPrincipal): int
    {
        $email = $this->option('email') ?? $this->ask('Email (used as login)', 'dav@carddav.test');
        $name = $this->option('name') ?? $this->ask('Display name', 'DAV User');
        $username = $this->option('username') ?? $this->ask('DAV username (principal)', config('dav.principal', 'admin'));
        $password = $this->option('password') ?? Str::password(16);

        if (! $this->option('password')) {
            $this->components->info("Generated password: {$password}");
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'dav_username' => $username,
                'password' => $password,
                'email_verified_at' => now(),
            ]
        );

        $davPrincipal->provision($user);

        $baseUrl = rtrim(config('app.url'), '/').'/'.trim(config('laravelsabre.path', 'dav'), '/');

        $this->newLine();
        $this->components->twoColumnDetail('Server URL', $baseUrl.'/');
        $this->components->twoColumnDetail('Username', $email);
        $this->components->twoColumnDetail('Password', $this->option('password') ? '(as provided)' : $password);
        $this->components->twoColumnDetail('Principal', $davPrincipal->principalUri($user));
        $this->newLine();
        $this->line('Configure iPhone/Android with the server URL above and these credentials.');
        $this->line('Use HTTPS in production (e.g. https://carddav.test/dav/).');

        return self::SUCCESS;
    }
}
