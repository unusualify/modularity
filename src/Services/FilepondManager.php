<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Image;
use Unusualify\Modularity\Entities\Filepond;
use Unusualify\Modularity\Entities\TemporaryFilepond;

class FilepondManager
{
    protected $tmp_file_path = 'public/fileponds/tmp/';

    protected $file_path = 'public/fileponds/';

    protected $session_prefix = '_filepond';

    public function createTemporaryFilepond(Request $request)
    {
        foreach (Arr::dot($request->allFiles()) as $input_role => $file) {
            $file_name = $file->getClientOriginalName();
            $folder_name = uniqid('', true);

            $tmp_file = TemporaryFilepond::create(compact('folder_name', 'file_name', 'input_role'));
            $file->storeAs($this->tmp_file_path . $folder_name, $file_name);

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

        $tmp_file = TemporaryFilepond::where('folder_name', trim(request()->getContent()))->first();

        if ($tmp_file) {

            Storage::deleteDirectory($this->tmp_file_path . $tmp_file->folder_name);

            // $this->deleteFilePondFromSession($tmp_file);
            Session::forget("{$this->session_prefix}.{$tmp_file->input_role}");

            $tmp_file->delete();

            return;
        }

    }

    public function previewFile($folder)
    {
        // dd($folder);
        if (Storage::exists($this->file_path . '/' . $folder)) {
            $path = Storage::files($this->file_path . '/' . $folder)[0];
        } else {
            $tmp_file = TemporaryFilepond::where('folder_name', $folder)->first();
            $path = $this->tmp_file_path . $tmp_file->folder_name . '/' . $tmp_file->file_name;
        }

        // dd($path);

        $storagePath = Storage::path($path);

        ob_end_clean(); // if I remove this, it does not work

        $fileType = pathinfo($storagePath, PATHINFO_EXTENSION);

        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
            $image = Image::make($storagePath);

            return $image->response($image->mime());
        } else {
            $mimeType = mime_content_type($storagePath);

            return response()->file($storagePath, [
                'Content-Type' => $mimeType,
            ]);
        }

        $image = Image::make($storagePath);

        return $image
            ->response($image->mime());
        // ->resize(300, 200)
        // ->response('jpg', 70);
    }

    public function persistFile(TemporaryFilepond $temp_filepond, Model $model, $role = null, $locale = null)
    {
        $temporary_path = $this->tmp_file_path . $temp_filepond->folder_name . '/' . $temp_filepond->file_name;

        $new_folder = $temp_filepond->folder_name;

        if (Storage::exists($temporary_path)) {

            $new_path = $this->file_path . $temp_filepond->folder_name . '/' . $temp_filepond->file_name;

            if (Storage::exists($new_path)) {
                Storage::delete($new_path);
            }

            $this->createFilepond($model, $temp_filepond, role: $role, locale: $locale);

            $this->deleteFilePondFromSession($temp_filepond);

            Storage::move($temporary_path, $new_path);

            Storage::deleteDirectory($temporary_path);

            rmdir(Storage::path($this->tmp_file_path . $temp_filepond->folder_name));

            $temp_filepond->delete();

            return $new_folder;
        }

        return '';

    }

    public function createFilepond($object, $temp_filepond, $role = null, $locale = null)
    {
        $filepondable_id = $object->id;
        $filepondable_type = get_class($object);

        Filepond::create([
            'filepondable_id' => $filepondable_id,
            'filepondable_type' => $filepondable_type,
            'file_name' => $temp_filepond->file_name,
            'role' => $role ?? $temp_filepond->input_role,
            'uuid' => $temp_filepond->folder_name,
            'locale' => $locale ?? config('app.locale', 'en'),
        ]);
    }

    public function saveFile($object, $files, $role, $locale = null)
    {
        $files ??= [];
        $existingUuids = array_column($files, 'uuid');
        $fileponds = $object->fileponds()->where('role', $role);

        if ($locale) {
            $fileponds = $fileponds->where('locale', $locale);
        }

        $fileponds = $fileponds->get();
        // files listesinde gelmeyip object->fileponds listesinde olanlari fileponds tablosundan ve storage'tan sil
        foreach ($fileponds as $file) {
            if (! in_array($file->uuid, $existingUuids)) {
                $file->delete();
            }
        }

        // dd($files, $fileponds);
        foreach ($files as $file) {
            if ((bool) $fileponds->select('uuid', $file['uuid']) && Storage::exists($this->file_path . $file['uuid'])) {
                continue;
            }

            $tmp_file = TemporaryFilepond::where('folder_name', $file['uuid'])->first();

            if ($tmp_file) {
                $this->persistFile($tmp_file, $object, role: $role, locale: $locale);
            }

        }

    }

    public function deleteFile($folder)
    {
        try {
            if (count(Storage::files($this->file_path . '/' . $folder)) > 0) {
                Storage::deleteDirectory($this->file_path . '/' . $folder);
                Filepond::where('uuid', $folder)->first()->delete();
            }
        } catch (\Throwable $th) {
            dd($folder, $this->file_path, $th, debug_backtrace());
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
     * @param string $key model->getTable() . $field_name
     * @param string $folder uniqid for filepond
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
     * @param string $key model->getTable() . $field_name
     * @param string $folder uniqid for filepond
     * @return void
     */
    public function deleteFilePondFromSession(TemporaryFilepond $tmp_file)
    {
        Session::forget("{$this->session_prefix}.{$tmp_file->input_role}");
    }

    /**
     * addToSession
     *
     * @param string $key model->getTable() . $field_name
     * @return string $folder uniqid for filepond
     */
    public function getFilePondsFromSession($role)
    {
        // $notations = Arr::dot(Session::get("{$this->session_prefix}"));
        $cacheFiles = Session::get("{$this->session_prefix}." . auth()->user()->id . '.' . $role);

        dd($cacheFiles);
        // $cache = data_get(Session::get("{$this->session_prefix}." . (auth()->user()->id ?? 0) . ".{$table}"), $notation);

        // $mapped = collect( data_get(Session::get("{$this->session_prefix}.{$table}"), $notation) )
        //     ->mapWithKeys(function($item,$i) use($notation){
        //         return [ str_replace('*', $i, $notation) => $item];
        //     })->toArray();
    }

    /**
     * Get file information for a given Filepond entity
     *
     * @param Filepond $filepond The Filepond entity to get information for
     * @return array The file information
     */
    public function getFileInfo($uuid)
    {
        $storageFile = $this->getStorageFile($uuid);


        // $fileSize = Storage::disk('local')->size($storagePath);
        // $mimeType = Storage::disk('local')->mimeType($storagePath);
        // $extension = pathinfo($filepond->file_name, PATHINFO_EXTENSION);
        // $lastModified = Storage::disk('local')->lastModified($storagePath);

        return [
            'size' => $storageFile ? Storage::size($storageFile) : 0,
            'type' => $storageFile ? Storage::mimeType($storageFile) : null,
            'name' => $storageFile ? basename($storageFile) : null,
            // 'exists' => true,
            // 'size' => $fileSize,
            // 'mime_type' => $mimeType,
            // 'extension' => $extension,
            // 'last_modified' => $lastModified,
            // 'size_formatted' => $this->formatFileSize($fileSize),
            // 'full_path' => storage_path('app/' . $storagePath)
        ];
    }

    /**
     * Format file size to human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatFileSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getStoragePath($uuid)
    {
        $path = null;
        if (Storage::exists($this->file_path . '/' . $uuid)) {
            $path = $this->file_path . '/' . $uuid;
        } else {
            $tmp_file = TemporaryFilepond::where('folder_name', $uuid)->first();

            if ($tmp_file) {
                $path = $this->tmp_file_path . $tmp_file->folder_name . '/' . $tmp_file->file_name;
            }
        }

        return $path;
    }

    /**
     * Get the storage path for a given UUID
     *
     * @param string $uuid The UUID of the filepond
     * @return string The storage path of the filepond
     */
    public function getStorageFile($uuid)
    {
        $path = $this->getStoragePath($uuid);

        if ($path) {
            return Storage::files($path)[0];
        }

        return null;
    }

    public function clearFolders()
    {
        $allFolders = Storage::directories($this->file_path);
        $excludedFolders = [
            trim($this->tmp_file_path, '/'),
        ];

        $excludedFolders = array_merge($excludedFolders, Filepond::all()->pluck('uuid')->map(fn ($uuid) => $this->file_path . $uuid)->toArray());

        $deleteFolders = array_values(array_diff($allFolders, $excludedFolders));

        foreach ($deleteFolders as $folder) {
            if (Storage::files($folder)) {
                Storage::deleteDirectory($folder);
            }
        }
    }

    public function clearTemporaryFiles($days = 7)
    {
        $query = TemporaryFilepond::where('created_at', '<', now()->subDays($days));
        $temporaryFileponds = $query->get();

        $deleteFolders = $temporaryFileponds->pluck('folder_name')->map(fn ($folder) => $this->tmp_file_path . $folder)->toArray();

        foreach ($deleteFolders as $folder) {
            if (Storage::files($folder)) {
                Storage::deleteDirectory($folder);
            }
        }

        $query->delete();

        // delete empty folders
        $allFolders = Storage::directories($this->tmp_file_path);
        $excludedFolders = TemporaryFilepond::all()->pluck('folder_name')->map(fn ($folder) => $this->tmp_file_path . $folder)->toArray();

        $deleteFolders = array_values(array_diff($allFolders, $excludedFolders));

        foreach ($deleteFolders as $folder) {
            if (Storage::files($folder)) {
                Storage::deleteDirectory($folder);
            }
        }

        return $temporaryFileponds;
    }

    public function getEncodedFile($folder)
    {

        try {
            $path = Storage::files($this->file_path . '/' . $folder)[0];
            // dd(Storage::path($path));

            return encodeImagePath(Storage::path($path));
        } catch (\Throwable $th) {

            return '';
        }

    }
}
