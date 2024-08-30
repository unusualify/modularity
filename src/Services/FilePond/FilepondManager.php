<?php

namespace Unusualify\Modularity\Services\Filepond;


use Unusualify\Modularity\Entities\TemporaryFilepond;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Image;
use Unusualify\Modularity\Entities\Filepond;

class FilepondManager
{

    protected $tmp_file_path = 'public/fileponds/tmp/';

    protected $file_path = 'public/fileponds/';

    protected $session_prefix = "_filepond";

    public function createTemporaryFilepond(Request $request)
    {
        foreach (Arr::dot($request->allFiles()) as $input_role => $file) {
            $file_name = $file->getClientOriginalName();
            $folder_name = uniqid('', true);

            $tmp_file = TemporaryFilepond::create(compact('folder_name', 'file_name', 'input_role'));
            $file->storeAs( $this->tmp_file_path . $folder_name, $file_name);

            // $this->addFilePondToSession($tmp_file);
            // $request->session()->put("{$this->session_prefix}.{$tmp_file->input_role}", $tmp_file->folder_name, 1440);
            Session::put("{$this->session_prefix}.{$tmp_file->input_role}", $tmp_file->folder_name, 1440);

            // Cookie::queue("{$this->cookie_prefix}.{$tmp_file->input_role}", $tmp_file->folder_name, 1440);

            // $object = session($this->session_prefix, []);
            // data_set($object, $tmp_file->input_role, $tmp_file->folder_name);
            // $request->session()->put($this->session_prefix, $object);

            return response($folder_name, 200);

        }

        return '';
    }

    public function deleteTemporaryFilepond(Request $request)
    {

        $tmp_file = TemporaryFilepond::where('folder_name', trim(request()->getContent()) )->first();

        if($tmp_file){

            Storage::deleteDirectory( $this->tmp_file_path . $tmp_file->folder_name);

            // $this->deleteFilePondFromSession($tmp_file);
            Session::forget("{$this->session_prefix}.{$tmp_file->input_role}");

            $tmp_file->delete();

            return;
        }

        return;
    }

    public function previewFile($folder)
    {
        // dd($folder);
        if(Storage::exists($this->file_path . '/' . $folder)){
            $path = Storage::files($this->file_path . '/' . $folder)[0] ;
        }else{
            $tmp_file = TemporaryFilepond::where('folder_name', $folder)->first();
            $path= $this->tmp_file_path . $tmp_file->folder_name . '/' .$tmp_file->file_name;
        }

        // dd($path);

        $storagePath = Storage::path($path);

        ob_end_clean(); // if I remove this, it does not work
        // dd($storagePath);
        $image = Image::make($storagePath);

        return $image
            ->response($image->mime());
            // ->resize(300, 200)
            // ->response('jpg', 70);
    }

    public function persistFile(TemporaryFilepond $temp_filepond, Model $model)
    {
        $temporary_path = $this->tmp_file_path . $temp_filepond->folder_name . '/' . $temp_filepond->file_name;

        $new_folder = $temp_filepond->folder_name;
        // dd($temporary_path, Storage::exists($temporary_path));
        if( Storage::exists($temporary_path) ){

            $new_path = $this->file_path . $temp_filepond->folder_name . '/'. $temp_filepond->file_name;

            if( Storage::exists($new_path)){
                Storage::delete($new_path);
            }

            Storage::move($temporary_path, $new_path);

            Storage::deleteDirectory( $temporary_path );

            rmdir( Storage::path( $this->tmp_file_path . $temp_filepond->folder_name ) );


            $this->createFilepond($model, $temp_filepond);
            $this->deleteFilePondFromSession($temp_filepond);
            $temp_filepond->delete();

            return $new_folder;
        }

        return '';

    }

    public function createFilepond($object, $temp_filepond)
    {
        $filepondable_id = $object->id;
        $filepondable_type = get_class($object);

        Filepond::create([
            'filepondable_id' => $filepondable_id,
            'filepondable_type' => $filepondable_type,
            'file_name' => $temp_filepond->file_name,
            'role' => $temp_filepond->input_role,
            'uuid' => $temp_filepond->folder_name,
            'locale' => 'tr',
        ]);
    }

    public function saveFile($object, $files, $role, $locale = 'tr')
    {
        $files ??= [];
        $exist_files = array_column($files, 'folder_name');
        $fileponds = $object->fileponds()->where('role', $role)->get();

        // dd($fileponds->select('uuid', $files[0]['folder_name']));
        // files listesinde gelmeyip object->fileponds listesinde olanlari fileponds tablosundan ve storage'tan sil
        foreach ($fileponds as $file) {
            if (!in_array($file->uuid, $exist_files)) {
                // dd($file->uuid, $exist_files);
                $file->delete();
                unset($files[array_search($file->uuid, $exist_files)]);
            }
        }

        foreach ($files as $folder) {

            if(!!$fileponds->select('uuid', $folder['folder_name']) && Storage::exists($this->file_path .$folder['folder_name'])){
                continue;
            };


            $tmp_file = TemporaryFilepond::where('folder_name', $folder['folder_name'])->first();
            // dump($tmp_file);
            if($tmp_file){
                // dump('persist');
                $this->persistFile($tmp_file, $object);
            }

        }

    }

    public function deleteFile($folder)
    {
        try {
            if( count( Storage::files($this->file_path . '/' . $folder)) > 0 ){
                Storage::deleteDirectory($this->file_path . '/' . $folder);
                Filepond::where('uuid', $folder)->first()->delete();
            }
        } catch (\Throwable $th) {
            dd( $folder, $this->file_path, $th, debug_backtrace() );
        }

    }

    public function getCachedFolders($object, $role)
    {
        $folders = [];

        foreach ($notations as $notation) {
            $folders = array_merge($folders, $this->getFilePondsFromSession($table, $notation));
        }

        return Arr::undot($folders);
    }

    /**
     * addToCookie
     *
     * @param  string $key model->getTable() . $field_name
     * @param  string $folder uniqid for filepond
     *
     * @return void
     */
    public function addFilePondToSession(TemporaryFilepond $tmp_file)
    {
        // Session::put("{$this->session_prefix}." . auth()->user()->id . "." . $tmp_file->input_role , $tmp_file->folder_name);
        // $cookie = cookie('name', 'value');
        // Cookie::queue("{$this->cookie_prefix}.{$tmp_file->input_role}", $tmp_file->folder_name, 1440);
    }

    /**
     * addToSession
     *
     * @param  string $key model->getTable() . $field_name
     * @param  string $folder uniqid for filepond
     *
     * @return void
     */
    public function deleteFilePondFromSession(TemporaryFilepond $tmp_file)
    {
        Session::forget("{$this->session_prefix}.{$tmp_file->input_role}");
    }

    /**
     * addToSession
     *
     * @param  string $key model->getTable() . $field_name
     *
     * @return string $folder uniqid for filepond
     */
    public function getFilePondsFromSession($role)
    {
        // $notations = Arr::dot(Session::get("{$this->session_prefix}"));
        $cacheFiles = Session::get("{$this->session_prefix}." . auth()->user()->id . "." . $role);

        dd($cacheFiles);
        // $cache = data_get(Session::get("{$this->session_prefix}." . (auth()->user()->id ?? 0) . ".{$table}"), $notation);



        // $mapped = collect( data_get(Session::get("{$this->session_prefix}.{$table}"), $notation) )
        //     ->mapWithKeys(function($item,$i) use($notation){
        //         return [ str_replace('*', $i, $notation) => $item];
        //     })->toArray();
    }

    public function getEncodedFile($folder)
    {

        try {
            $path = Storage::files($this->file_path . '/' . $folder)[0];
            // dd(Storage::path($path));

            return encodeImagePath( Storage::path($path) );
        } catch (\Throwable $th) {

            return '';
        }


    }


}
