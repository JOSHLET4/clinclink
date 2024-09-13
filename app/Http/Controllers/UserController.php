<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\FileHandler;
use App\Utils\SimpleCRUD;
use App\Utils\SimpleJSONResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public $crud;

    public function __construct()
    {
        $this->crud = new SimpleCRUD(new User);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->crud->index(UserResource::class, $request->pagination);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        $authController = new AuthController();
        return $authController->register($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        return $this->crud->show($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $password = $request->input('password');
        $request->merge(['password' => bcrypt($password)]);
        return $this->crud->update(
            FileHandler::handleSingleFileUpload($request, 'profile_picture_uri'),
            $id,
            null
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->crud->destroy($id);
    }
}
