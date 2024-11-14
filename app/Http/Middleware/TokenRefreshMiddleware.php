<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class TokenRefreshMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json(['error' => 'Token no proporcionado'], 401);
            }

            $payload = JWTAuth::getPayload($token)->toArray();
            $expiration = $payload['exp'] - time();

            // Si el token expirará en menos de 30 minutos, renovarlo
            if ($expiration < 1800) {
                $newToken = JWTAuth::refresh($token);
                $response = $next($request);

                // Agregar el nuevo token a la respuesta
                return $this->setAuthenticationHeader($response, $newToken);
            }

            return $next($request);

        } catch (TokenExpiredException $e) {
            try {
                $newToken = JWTAuth::refresh(JWTAuth::getToken());
                $request->headers->set('Authorization', 'Bearer ' . $newToken);

                $response = $next($request);
                return $this->setAuthenticationHeader($response, $newToken);

            } catch (JWTException $e) {
                Log::error('Error al refrescar token', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Token expirado y no se puede refrescar'], 401);
            }
        } catch (JWTException $e) {
            Log::error('Error de token JWT', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Token inválido'], 401);
        }
    }

    protected function setAuthenticationHeader($response, $token)
    {
        $response->headers->set('Authorization', 'Bearer ' . $token);
        $cookieExpiry = 60 * 24; // 24 horas
        cookie('jwt_token', $token, $cookieExpiry, '/', null, true, true);
        return $response;
    }
}
?>
