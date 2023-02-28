<?php
namespace OoBook\CRM\Base\Traits;

use Illuminate\Http\Request;

trait Searchable {

    public function paginate(Request $request)
    {
        $search = $request->query('search') ?? "";

        $itemsPerPage = $request->query('itemsPerPage') ?? 5;


        $search_columns = collect( getModuleSubRoute($this->name.'.table.headers') ?? [] )->filter(function ($value) {
            return isset($value['searchable']) ? $value['searchable'] : false;
        })->map(function($value){
            return $value['value'];
        })->toArray();
        // dd($search_columns);
        // dd( app($this->model), app($this->model)->getAttributes() );

        if($search != ""){
            $items = $this->model::where(function($query) use($search, $search_columns){
                foreach ($search_columns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
                // dd($query->toSql());
            })->paginate($itemsPerPage);
            // dd($items);
        }else{
            $items =  $this->model::paginate($itemsPerPage);
        }

        return $items;

    }

}
