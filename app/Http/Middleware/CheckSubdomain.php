<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;

class CheckSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next){
        $origin = parse_url($request->header('Origin'));
        $host = $origin['host'];
        $parts = explode('.', $host);

        if(count($parts) >= 2){
            $subdomain = $parts[0];
            $school = School::where('school_domain', $subdomain)->first();

            if(isset($school)){
                return response()->json($school, 200);
            }
            else{
                return response()->json('School not found', 404);
            }
        }

        return response()->json('main', 200);
    }
}
