<?php

namespace Unusualify\Modularity\Services\Filepond;


use Unusualify\Modularity\Entities\TemporaryAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Image;
use Unusualify\Modularity\Entities\Asset;

class FilepondManager
{

    protected $tmp_file_path = 'public/fileponds/tmp/';

    protected $file_path = 'public/fileponds/';

    protected $session_prefix = "filepond";

    public function createTemporaryAsset(Request $request)
    {


        $model = $request->model;
        foreach (Arr::dot($request->allFiles()) as $name => $file) {
            # code...

            $file_name = $file->getClientOriginalName();
            $folder = uniqid('', true);
            // dd($name, $folder);


    // $this->addFilePondToSession($model . "." . $name , $folder);
            $file->storeAs( $this->tmp_file_path . $folder, $file_name);

            TemporaryAsset::create([
                'folder_name' => $folder,
                'file_name' => $file_name,
                'input_role' => $name,
            ]);


            return $folder;
        }

        return '';
    }




    public function deleteTemporaryAsset(Request $request)
    {

        $tmp_file = TemporaryAsset::where('folder_name', trim(request()->getContent()) )->first();

        if($tmp_file){

            Storage::deleteDirectory( $this->tmp_file_path . $tmp_file->folder_name);

            // $this->deleteFilePondFromSession($table . '.' . $tmp_file->input_name);

            $tmp_file->delete();

            return;
        }

        return;
    }

    public function previewFile($folder)
    {
        if(Storage::exists($this->file_path . '/' . $folder)){
            $path = Storage::files($this->file_path . '/' . $folder)[0] ;
        }else{
            $tmp_file = TemporaryAsset::where('folder', $folder)->first();
            $path= $this->tmp_file_path . $tmp_file->folder . '/' .$tmp_file->file;
        }

        // dd($path);

        $storagePath = Storage::path($path);

        ob_end_clean(); // if I remove this, it does not work

        return Image::make($storagePath)
            // ->resize(300, 200)
            ->response('jpg', 70);
    }



    public function persistFile(TemporaryAsset $temp_asset, Model $model)
    {
        $temporary_path = $this->tmp_file_path . $temp_asset->folder_name . '/' . $temp_asset->file_name;

        $new_folder = $temp_asset->folder_name;

        if( Storage::exists($temporary_path) ){

            $new_path = $this->file_path . $temp_asset->folder_name . '/'. $temp_asset->file_name;

            if( Storage::exists($new_path)){
                Storage::delete($new_path);
            }

            Storage::move($temporary_path, $new_path);

            Storage::deleteDirectory( $temporary_path );

            rmdir( Storage::path( $this->tmp_file_path . $temp_asset->folder_name ) );


            $this->createAsset($model, $temp_asset);
            $temp_asset->delete();

            // $this->deleteFilePondFromSession( $table . '.' . $temp_asset->input_name);
            return $new_folder;
        }

        return '';

    }

    public function createAsset($object, $temp_asset)
    {
        $assetable_id = $object->id;
        $assetable_type = get_class($object);

        Asset::create(
            [
                'assetable_id' => $assetable_id,
                'assetable_type' => $assetable_type,
                'file_name' => $temp_asset->file_name,
                'role' => $temp_asset->input_role,
                'uuid' => $temp_asset->folder_name,
                'locale' => 'tr',
            ]
        );
    }


    public function saveFile($files, $object){

        $fileFolderNames = array_column($files, 'folderName');

        // files listesinde gelmeyip object->assets listesinde olanlari assets tablosundan ve storage'tan sil
        foreach ($object->getAssets() as $file) {
            if (!in_array($file->uuid, $fileFolderNames)) {
                $file->delete();
                unset($files[array_search($file->uuid, $fileFolderNames)]);
            }
        }


        foreach ($files as $folder) {

            // dd(
            //     'asdsad',
            //     Storage::exists($folder)
            // );


            if(!!$object->assets()->get()->select('uuid', $folder['folderName']) && Storage::exists($folder['folderName'])) return;


            $tmp_file = TemporaryAsset::where('folder_name', $folder['folderName'])->first();


            if(!!$tmp_file){
                return $this->persistFile($tmp_file, $object);
            }

        }

    }

    public function deleteFile($folder){
        try {
            if( count( Storage::files($this->file_path . '/' . $folder)) > 0 ){
                Storage::deleteDirectory($this->file_path . '/' . $folder);
                Asset::where('uuid', $folder)->first()->delete();
            }
        } catch (\Throwable $th) {
            dd( $folder, $this->file_path, $th, debug_backtrace() );
        }

    }

    public function getCachedFolders($table, $notations)
    {
        $folders = [];

        foreach ($notations as $notation) {
            $folders = array_merge($folders, $this->getFilePondsFromSession($table, $notation));
        }

        return Arr::undot($folders);
    }

    /**
     * addToSession
     *
     * @param  string $key model->getTable() . $field_name
     * @param  string $folder uniqid for filepond
     *
     * @return void
     */
    public function addFilePondToSession(string $key, string $folder)
    {
        // $folders = Session::get('folders', []);

        // array_push($folders, $folder);

        // Session::put('folders', $folders);
        // Session::push('folders', $folder);

        // Session::push('folders', 'beli');
        Session::put("{$this->session_prefix}." . auth()->user()->id . "." . $key  , $folder);
    }
    /**
     * addToSession
     *
     * @param  string $key model->getTable() . $field_name
     * @param  string $folder uniqid for filepond
     *
     * @return void
     */
    public function deleteFilePondFromSession(string $key)
    {
        Session::forget("{$this->session_prefix}." . auth()->user()->id . "." . $key  );
    }

    /**
     * addToSession
     *
     * @param  string $key model->getTable() . $field_name
     *
     * @return string $folder uniqid for filepond
     */
    public function getFilePondsFromSession($table, $notation)
    {
        // $notations = Arr::dot(Session::get("{$this->session_prefix}"));

        $cache = data_get(Session::get("{$this->session_prefix}." . (auth()->user()->id ?? 0) . ".{$table}"), $notation);

        $mapped = [];

        if(is_array($cache)){
            array_walk( $cache, function($value, $i) use(&$mapped,$notation){
                $mapped[str_replace('*', $i, $notation)] = $value;
            });

        }else if(!!$cache){
            $mapped[$notation] = $cache;
        }

        return $mapped;

        // $mapped = collect( data_get(Session::get("{$this->session_prefix}.{$table}"), $notation) )
        //     ->mapWithKeys(function($item,$i) use($notation){
        //         return [ str_replace('*', $i, $notation) => $item];
        //     })->toArray();
    }

    public function getEncodedFile($folder){

        try {
            $path = Storage::files($this->file_path . '/' . $folder)[0];
            // dd(Storage::path($path));

            return encodeImagePath( Storage::path($path) );
        } catch (\Throwable $th) {

            return '';
        }


    }


}
