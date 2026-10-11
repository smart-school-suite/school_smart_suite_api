<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO;

    /**
     * @return array<int, string>|string|null
     */
    protected function proxies()
    {
        if (! is_null(static::$alwaysTrustProxies)) {
            return static::$alwaysTrustProxies;
        }

        $proxies = config('trusted-proxies.at');

        if ($proxies === '*' || $proxies === '**') {
            return $proxies;
        }

        if (is_array($proxies)) {
            return $proxies;
        }

        return array_map('trim', explode(',', (string) $proxies));
    }

    /**
     * @return int
     */
    protected function headers()
    {
        return static::$alwaysTrustHeaders ?? config('trusted-proxies.headers');
    }
}
