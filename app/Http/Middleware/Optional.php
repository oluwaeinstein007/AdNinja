<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Auth\Factory as Auth;

class Optional
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');
        if($header) {
          // call auth middleware and get user information
          $user = $this->auth->guard('sanctum')->user();
          // pass user to auth user
          $request->authUser = $user;
          // check if user is authenticated
          $this->auth->guard('sanctum')->user();
        }
        return $next($request);
      }
}
