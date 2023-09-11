<?php

namespace App\Http\Middleware;

use Closure;

class CheckFeatureFlag
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $configKey)
    {
        abort_unless(config('mailcare.'.$configKey), 403);

        return $next($request);
    }
}
