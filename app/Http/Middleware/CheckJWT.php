<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class CheckJWT
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verificar el token de la cookie
            $token = $request->cookie('jwt_token');
            if (!$token) {
                return redirect('/login');
            }

            // Verificar si el token es válido
            JWTAuth::setToken($token);
            if (!JWTAuth::authenticate()) {
                return redirect('/login');
            }

            return $next($request);
        } catch (TokenExpiredException $e) {
            return redirect('/login');
        } catch (TokenInvalidException $e) {
            return redirect('/login');
        } catch (\Exception $e) {
            return redirect('/login');
        }
    }
}
?>
