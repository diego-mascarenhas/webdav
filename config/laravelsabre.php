<?php

return [

    'domain' => null,

    'path' => 'dav',

    'enabled' => env('LARAVELSABRE_ENABLED', true),

    /*
    | DAV clients use WebDAV methods (PROPFIND, REPORT, etc.) that must not
    | pass through CSRF or session middleware.
    */
    'middleware' => [],

];
