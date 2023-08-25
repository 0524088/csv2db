<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Session;

class AjaxCheckLoginStatus
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
        // 登入狀態
        if(Session::has('token')) {
            // 是否異地登入
            $user = User::where('token', Session::get('token'))->first();
            if($user !== null && $user->exists === true) return $next($request);
            else {
                // 異地登入-清除session
                Session::flush();
                return response(['status' => 'error', 'message' => '請重新登入'], 400);
            }
            
        }
        return response(['status' => 'error', 'message' => '請重新登入'], 400);
    }
}
