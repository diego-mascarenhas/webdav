<?php

return [

    'realm' => env('DAV_REALM', 'CardDAV'),

    'principal' => env('DAV_PRINCIPAL', 'admin'),

    /*
    | Token for machine-to-machine access (e.g. humano.app pulling contacts).
    | Header: Authorization: Bearer {token}  or  X-Dav-Api-Token: {token}
    */
    'api_token' => env('DAV_API_TOKEN'),

];
