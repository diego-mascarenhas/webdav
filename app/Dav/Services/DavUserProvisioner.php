<?php

namespace App\Dav\Services;

use App\Auth\DavUserResolver;
use App\Models\User;
use Illuminate\Support\Str;

class DavUserProvisioner
{
    public function __construct(
        private readonly DavPrincipalService $davPrincipal,
    ) {
    }

    /**
     * @return array{user: User, password: string, created: bool}
     */
    public function create(
        string $email,
        string $name,
        ?string $davUsername = null,
        ?string $password = null,
    ): array {
        $plainPassword = $password ?? Str::password(16);
        $username = $davUsername ?? strtok($email, '@') ?: config('dav.principal', 'admin');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'dav_username' => $username,
                'password' => $plainPassword,
                'email_verified_at' => now(),
            ]
        );

        $this->davPrincipal->provision($user);

        return [
            'user' => $user->fresh(),
            'password' => $plainPassword,
            'created' => $user->wasRecentlyCreated,
        ];
    }

    public function link(string $email, string $password): ?User
    {
        return DavUserResolver::validate($email, $password);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->password = $password;
        $user->save();

        return $user->fresh();
    }

    /**
     * @return array{email: string, dav_username: string, name: string, principal: string, dav_url: string, password?: string}
     */
    public function toAccountPayload(User $user, ?string $plainPassword = null): array
    {
        $baseUrl = rtrim((string) config('app.url'), '/').'/'.trim((string) config('laravelsabre.path', 'dav'), '/');

        $payload = [
            'email' => $user->email,
            'dav_username' => $user->dav_username ?? strtok($user->email, '@'),
            'name' => $user->name,
            'principal' => $this->davPrincipal->principalUri($user),
            'dav_url' => $baseUrl.'/',
        ];

        if ($plainPassword !== null) {
            $payload['password'] = $plainPassword;
        }

        return $payload;
    }
}
