<?php

namespace OoBook\CRM\Base\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class LanguageController extends Controller
{
    private $translation;

    public function __construct(\JoeDixon\Translation\Drivers\Translation $translation)
    {
        $this->translation = $translation;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $languages = $this->translation->allLanguages();

        $translations = $languages->map(function($lang){
            $translation = $this->translation->allTranslationsFor($lang);
            $translations = [];
            foreach ($translation as $name => $value) {
                $original = $value->toArray();

                foreach ($original as $key => $array) {
                    $multidimensional = [];
                    foreach ($array as $notation => $item) {
                        Arr::set($multidimensional, $notation, $item);
                    }
                    $original[$key] = $multidimensional;
                }

                $translations[$name] = $original;
            }


            return array_merge_recursive_preserve($translations['group'], isset($translations['single']['single']) ?  $translations['single']['single'] : []);
            return trans()->get('*', [], $lang);
        });
        // dd(
        //     trans()->get('*', [], 'tr'),
        //     \Lang::get('*'),

        //     trans('auth'),
        //     \Lang::get('auth'),
        //     __('auth'),
        //     ___('auth'),

        //     trans('authentication'),
        //     \Lang::get('authentication'),
        //     __('authentication'),
        //     ___('authentication'),

        //     $groups = $this->translation->getGroupsFor(config('app.locale'))->merge('single'),
        //     $translations = $this->translation->filterTranslationsFor('tr', $request->get('filter'))
        // );
        return response()->json($translations);
        // dd(
        //     $languages,
        //     $this->translation->getSourceLanguageTranslationsWith('tr'),
        //     $translations,
        //     trans()->get('*', [], 'en'),
        //     json_encode(trans()->get('*')),
        //     trans('errors.key_exists')
        // );
        // return new RoleResource( Role::paginate( request()->query('itemsPerPage') ?? 10) );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
