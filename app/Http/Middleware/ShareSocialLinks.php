<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SocialLink;
use Illuminate\Support\Facades\View;

class ShareSocialLinks
{
    public function handle($request, Closure $next)
    {
        $social = SocialLink::firstOrCreate(['id' => 1]);
        View::share('social', $social);
        
        return $next($request);
    }
}