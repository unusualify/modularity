<?php

namespace Modules\SystemUser\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\SystemUser\Repositories\RoleRepository;
use Modules\SystemUser\Transformers\RoleResource;

class RoleController extends Controller
{
    /**
     * This resource repository
     */
    private $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        return new RoleResource($this->repository->paginate($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     * @return Renderable
     */
    public function show($id) {}

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
