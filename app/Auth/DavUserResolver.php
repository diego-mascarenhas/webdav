<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DavUserResolver
{
    public static function findByLogin(string $login): ?User
    {
        $login = trim($login);

        if ($login === '') {
            return null;
        }

        return User::query()
            ->where('email', $login)
            ->orWhere('dav_username', $login)
            ->orWhere('name', $login)
            ->first();
    }

    public static function validate(string $login, string $password): ?User
    {
        $user = self::findByLogin($login);

        if ($user === null || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
