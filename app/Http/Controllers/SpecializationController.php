<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use App\Utils\SimpleCRUD;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    public $crud;

    public function __construct()
    {
        $this->crud = new SimpleCRUD(new Specialization);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->crud->index(null, $request->pagination);
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
    public function store(Request $request): JsonResponse
    {
        return $this->crud->store($request);
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
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->crud->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->crud->destroy($id);
    }
}
