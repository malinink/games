<?php
/*
 *
 * @artesby
 */
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user()->isAdmin == '0') {
            return redirect('home')->with('message', 'Permission denied');
        }
        return $next($request);
    }
}
