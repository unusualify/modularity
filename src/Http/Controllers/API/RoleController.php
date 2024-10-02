<?php

namespace Unusualify\Modularity\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Http\Requests\RoleRequest;
use Unusualify\Modularity\Repositories\RoleRepository;
use Unusualify\Modularity\Transformers\RoleResource;

class RoleController extends Controller
{
    /**
     * @var roleRepository
     */
    private $repository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->repository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(Request $request)
    {
        return new RoleResource($this->repository->paginate($request));
        // return new RoleResource( Role::paginate( request()->query('itemsPerPage') ?? 10) );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {

        return $this->repository->create($request->all());

        dd($request->validated(), $request->safe(), $request->safe()->only(['name']), $request->safe()->except(['name']));

        $validated = $request->validated();

        // Retrieve a portion of the validated input data...
        $validated = $request->safe()->only(['name']);
        $validated = $request->safe()->except(['name']);

        $validated['guard_name'] = 'web';

        $role = Role::create($validated);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, $id)
    {
        return $this->repository->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->repository->delete($id);
    }
}
