<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Utils\FileHandler;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Utils\SimpleCRUD;
use App\Utils\SimpleJSONResponse;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public $crud;

    public function __construct()
    {
        $this->crud = new SimpleCRUD(new User);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $password = $request->input('password');
        $request->merge(['password' => bcrypt($password)]);
        return $this->crud->store(
            FileHandler::handleSingleFileUpload($request, 'profile_picture_uri'),
            null
        );
    }
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', '=', $request->email)->first();
        if (isset($user->id)) {
            if (Hash::check($request->password, $user->password)) {
                // creamos el tokken
                $token = $user->createToken('auth_token')->plainTextToken;
                // si todo ok
                return SimpleJSONResponse::successResponse(
                    [
                        'user_data' => $user,
                        'tokken' => $token
                    ],
                    'Usuario logueado exitosamente',
                    200
                );
            } else {
                return SimpleJSONResponse::errorResponse(
                    'Contrasenia incorrecta',
                    400
                );
            }
        } else {
            return SimpleJSONResponse::errorResponse(
                'Usuario no encontrado',
                400
            );
        }
    }
    public function userProfile(): JsonResponse
    {
        return SimpleJSONResponse::successResponse(
            auth()->user(),
            'Informacion de prueba de informacion de usuario',
            200
        );
    }
    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();
        return SimpleJSONResponse::successResponse(
            null,
            'Cerro session exitosamente',
            200
        );
    }
}
