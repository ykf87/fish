<?php

namespace App\Http\Middleware;

use App\Globals\Region;
use Closure;
use Illuminate\Http\Request;

class GetRegion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->get('_region')) {
            return $next($request);
        }

        $region = Region::GetRegionByIp($request->ip);
        if ($region) {
            $request->merge(['_region' => $region]);
        }

        return $next($request);
    }
}
