<?php

namespace OoBook\CRM\Base\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OoBook\CRM\Base\Http\Requests\StoreRoleRequest;
use OoBook\CRM\Base\Http\Requests\RoleRequest;
use OoBook\CRM\Base\Repositories\RoleRepository;
use OoBook\CRM\Base\Transformers\RoleResource;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    /**
     * @var roleRepository
     */
    private $repository;

    /**
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->repository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        return new RoleResource( $this->repository->paginate($request) );
        // return new RoleResource( Role::paginate( request()->query('itemsPerPage') ?? 10) );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {

        return $this->repository->create( $request->all() );


        dd( $request->validated(), $request->safe(),$request->safe()->only(['name']), $request->safe()->except(['name']) );

        $validated = $request->validated();

        // Retrieve a portion of the validated input data...
        $validated = $request->safe()->only(['name']);
        $validated = $request->safe()->except(['name']);

        $validated['guard_name'] = "web";

        $role = Role::create($validated);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, $id)
    {
        return $this->repository->update( $id, $request->all() );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->repository->delete($id);
    }
}
