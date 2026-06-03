<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $addHttpCookie = false;

    protected $except = [
        'solar-systems/*/panels/*',
        'interventions/*',
        'solar-systems/*/productions/*',
        'alerts/*',
        'fault-simulations/*',
        'panels/*',
        'solar-systems/*',
        'api/*',
        '/generate-demo-data',
    ];
}
