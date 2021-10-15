<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherEnrolledInCourse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $is_enrolled = $request->user()->isEnrolledAs('TeacherEnrollment', $request->courseId);
        if($is_enrolled || $request->user()->tokenCan('role:admin')){
            return $next($request);
        }else{
            abort(401);
        }
    }
}
