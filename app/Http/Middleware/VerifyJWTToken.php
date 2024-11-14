<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class VerifyJWTToken
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!auth()->user()) {
                return redirect('/login');
            }
        } catch (TokenExpiredException $e) {
            return redirect('/login')->with('error', 'Token expirado');
        } catch (TokenInvalidException $e) {
            return redirect('/login')->with('error', 'Token inválido');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error de autenticación');
        }

        return $next($request);
    }
}
