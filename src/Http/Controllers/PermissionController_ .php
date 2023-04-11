<?php

namespace Unusual\CRM\Base\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusual\CRM\Base\Http\Requests\StorePermissionRequest;
use Unusual\CRM\Base\Transformers\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $headers = [
            [
                'text' => 'Name',
                'align' => 'start',
                'sortable' => false,
                'value' => 'name',
            ],
            [
                'text' => 'Guard Name',
                'value' => 'guard_name',
            ],
            [
                'text' => 'Created Time',
                'value' => 'created_at',
                'formatter' => 'formatDate'
            ],
            [
                'text' => 'Actions',
                'value' => 'actions',
                'sortable' => false
            ],
        ];

        $inputs = [
            [
                'title' => 'Name',
                'name' => 'name',
                'type' => 'text',
                'placeholder' => 'admin',
                'cols' => 12,
                'sm' => 12,
                'md' => 8
            ],
            [
                'title' => 'Guard Name',
                'name' => 'guard_name',
                'type' => 'text',
                'placeholder' => 'web',
                'cols' => 12,
                'sm' => 12,
                'md' => 8
            ]
        ];

        return view('base::permission.index', compact('headers', 'inputs'));
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('base::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function apiStore(StorePermissionRequest $request)
    {
        $validated = $request->validated();

        // Retrieve a portion of the validated input data...
        $validated = $request->safe()->only(['name']);
        $validated = $request->safe()->except(['name']);

        $validated['guard_name'] = "web";

        $role = Permission::create($validated);
        return view('base::create');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('base::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('base::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
