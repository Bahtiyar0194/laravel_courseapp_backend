<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Color;
use App\Models\Font;

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

        if(count($parts) == 1 && $parts[0] == 'localhost'){
            return response()->json('main', 200);
        }
        elseif(count($parts) == 2 && $parts[1] != 'localhost'){
            return response()->json('main', 200);
        }
        else{
            $subdomain = $parts[0];
            $school = School::where('school_domain', $subdomain)
            ->first();

            if(isset($school)){
                $school->title_font_class = Font::where('font_id', '=', $school->title_font_id)->first()->font_class.'_title';
                $school->body_font_class = Font::where('font_id', '=', $school->body_font_id)->first()->font_class.'_body';
                $school->color_scheme_class = Color::where('color_id', '=', $school->color_id)->first()->color_class;

                return response()->json($school, 200);
            }
            else{
                return response()->json('School not found', 404);
            }
        }

        return response()->json('main', 200);
    }
}
