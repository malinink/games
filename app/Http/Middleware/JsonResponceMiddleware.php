<?php
/**
 * @author artesby
 */
namespace App\Http\Middleware;

use Closure;

class JsonResponceMiddleware
{
    const METHOD = 'POST';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->method() == self::METHOD) {
            $request->merge(json_decode($request->getContent(), true));
        }
        
        return $next($request);
    }
}
