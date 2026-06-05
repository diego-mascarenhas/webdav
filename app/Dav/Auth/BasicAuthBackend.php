<?php

namespace App\Dav\Auth;

use App\Auth\DavUserResolver;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\HTTP\Auth\Basic;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class BasicAuthBackend extends AbstractBasic
{
    public function __construct()
    {
        $this->realm = config('dav.realm', 'CardDAV');
        $this->principalPrefix = 'principals/';
    }

    public function check(RequestInterface $request, ResponseInterface $response): array
    {
        $auth = new Basic($this->realm, $request, $response);
        $userpass = $auth->getCredentials();

        if (! $userpass) {
            return [false, "No 'Authorization: Basic' header found."];
        }

        $user = DavUserResolver::validate($userpass[0], $userpass[1]);

        if ($user === null) {
            return [false, 'Username or password was incorrect'];
        }

        $principalName = $user->dav_username ?? strtok($user->email, '@');

        return [true, $this->principalPrefix.$principalName];
    }

    protected function validateUserPass($username, $password): bool
    {
        return DavUserResolver::validate($username, $password) !== null;
    }
}
