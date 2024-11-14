<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class JWTAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken() ?? $request->cookie('jwt_token');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token inválido'
                ], 401);
            }

            auth()->login($user);
            return $next($request);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expirado'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la autenticación: ' . $e->getMessage()
            ], 401);
        }
    }

    protected function getTokenFromRequest(Request $request)
    {
        // Intentar obtener el token del header de Authorization
        $token = $request->bearerToken();

        // Si no está en el header, intentar obtenerlo de la cookie
        if (!$token) {
            $token = $request->cookie('jwt_token');
        }

        // Si aún no hay token, intentar obtenerlo de otros lugares
        if (!$token) {
            $token = $request->input('token');
        }

        return $token;
    }
}
?>
