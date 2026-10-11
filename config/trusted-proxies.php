<?php

use Illuminate\Http\Request;

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | IP addresses or CIDR ranges of reverse proxies (e.g. Nginx Proxy Manager
    | on the Docker network). Use "*" when the app is only reachable internally
    | and every upstream is your proxy.
    |
    */

    'at' => env('TRUSTED_PROXIES', '172.16.0.0/12,10.0.0.0/8,192.168.0.0/16'),

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxy Headers
    |--------------------------------------------------------------------------
    |
    | Headers set by Nginx Proxy Manager so Laravel detects HTTPS and the
    | real client IP (prevents http:// asset URLs and mixed content).
    |
    */

    'headers' => Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO,

];
