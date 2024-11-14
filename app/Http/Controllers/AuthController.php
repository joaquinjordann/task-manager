<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[\pL\s]+$/u'
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                ]
            ], [
                'name.required' => 'El nombre es obligatorio',
                'name.regex' => 'El nombre solo puede contener letras y espacios',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'Ingresa un correo electrónico válido',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.regex' => 'La contraseña debe contener al menos una letra minúscula, una mayúscula, un número y un carácter especial'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $token = Auth::login($user);

            $this->setTokenCookie($token);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario creado exitosamente. Por favor, inicia sesión.',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en registro de usuario', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el usuario'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            $user = auth()->user();

            // Crear cookie segura
            $cookie = cookie(
                'jwt_token',
                $token,
                60, // duración en minutos
                '/',
                null,
                config('app.env') === 'production',
                true
            );

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ])->withCookie($cookie);

        } catch (\Exception $e) {
            Log::error('Error en login', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el inicio de sesión'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            $newToken = JWTAuth::refresh($token);
            $this->setTokenCookie($newToken);

            return response()->json([
                'status' => 'success',
                'authorization' => [
                    'token' => $newToken,
                    'type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60 // TTL en segundos
                ]
            ]);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'El token ha expirado y no puede ser refrescado'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido'
            ], 401);
        } catch (\Exception $e) {
            Log::error('Error al refrescar token', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al refrescar el token'
            ], 500);
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            $this->removeTokenCookie();

            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en logout', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }

    protected function setTokenCookie($token)
    {
        cookie()->queue(
            'jwt_token',
            $token,
            config('jwt.ttl'), // usar TTL de la configuración
            '/',
            null,
            true, // solo https en producción
            true  // httpOnly
        );
    }

    protected function removeTokenCookie()
    {
        cookie()->queue(cookie()->forget('jwt_token'));
    }
}
?>
