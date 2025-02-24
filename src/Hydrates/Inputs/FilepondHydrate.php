<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class FilepondHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'default' => [],

        // keynote: application/x-iwork-keynote-sffkey
        // pages: application/x-iwork-pages-sffpages
        // numbers: application/x-iwork-numbers-sffnumbers
        // pdf: application/pdf,
        // doc: application/msword,
        // docx: application/vnd.openxmlformats-officedocument.wordprocessingml.document
        'accepted-file-types' => [ // Acceptable file types - however not working well for now
            'image/*, file/*',
        ],

        // Multiple file upload functionalities
        'allow-multiple' => true,
        'max-files' => 2,

        // Other Functionalities
        'allow-drop' => true,
        'allow-replace' => true, // only works when allowMultiple is false
        'allow-remove' => true,
        'allow-reorder' => false,
        'allow-process' => true,
        'allow-image-preview' => false,

        // Drag-Drop Properties
        'drop-on-page' => false, // FilePond will catch all files dropped on the webpage
        'drop-on-element' => true, // Require drop on the FilePond element itself to catch the file.
        'drop-validation' => false, // When enabled, files are validated before they are dropped. A file is not added when it's invalid.
    ];

    public $acceptedExtensionMaps = [
        ".csv" => "text/csv",
        ".doc" => "application/msword",
        ".docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        ".pdf" => "application/pdf",
        ".pages" => "application/x-iwork-pages-sffpages",
        ".numbers" => "application/x-iwork-numbers-sffnumbers",
        ".key" => "application/x-iwork-keynote-sffkey",
        ".ppt" => "application/vnd.ms-powerpoint",
        ".pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        ".xls" => "application/vnd.ms-excel",
        ".xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ".dbf" => "application/dbf",
        ".geoJSon" => "application/vnd.geo+json",
        ".gml" => "application/gml+xml",
        ".kml" => "application/vnd.google-earth.kml+xml",
        ".kmz" => "application/vnd.google-earth.kmz",
        ".prj" => "application/octet-stream",
        ".sbn" => "application/octet-stream",
        ".sbx" => "application/octet-stream",
        ".shp" => "application/octet-stream",
        ".shpz" => "application/octet-stream",
        ".shx" => "application/octet-stream",
        ".wkt" => "application/octet-stream",
        ".txt" => "text/plain",
        ".rtf" => "application/rtf",
        ".zip" => "application/zip",
        ".rar" => "application/x-rar-compressed",
        ".7z" => "application/x-7z-compressed",
        ".tar" => "application/x-tar",
        ".gz" => "application/gzip",
        ".mp3" => "audio/mpeg",
        ".wav" => "audio/wav",
        ".mp4" => "video/mp4",
        ".avi" => "video/x-msvideo",
        ".mov" => "video/quicktime",
        ".jpg" => "image/jpeg",
        ".jpeg" => "image/jpeg",
        ".png" => "image/png",
        ".gif" => "image/gif",
        ".svg" => "image/svg+xml",
        ".xml" => "application/xml",
        ".json" => "application/json",
        ".html" => "text/html",
        ".css" => "text/css",
        ".js" => "application/javascript",
        ".odt" => "application/vnd.oasis.opendocument.text",
        ".ods" => "application/vnd.oasis.opendocument.spreadsheet",
        ".odp" => "application/vnd.oasis.opendocument.presentation",
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        // Input type casting
        $input['type'] = 'input-filepond';
        // In order to toggle of credits
        $input['credits'] = false;

        $input['inputName'] = $input['name'] ?? 'filepond';
        // $input['label'] ??= 'filepond';

        $input['max-files'] = $input['max'] ?? $input['max-files'];

        $input['endPoints'] = [
            'process' => route('filepond.process'),
            'revert' => route('filepond.revert'), // for deleting temp files
            'load' => str_replace(':id', '', route('filepond.preview', ['uuid' => ':id'])),
        ];

        if(isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])){
            $input['accepted-file-types'] = $this->getAcceptedFileTypes($input['acceptedExtensions']);
            unset($input['acceptedExtensions']);
            // dd($input);
        }

        // Custom Labels
        $input['label-idle'] ??= __('Drag & Drop your files or Browse');

        $input['label-invalid-field'] ??= __('filepon-invalid-field-label');
        $input['label-file-loading'] ??= __('filepond-loading-lable');
        $input['label-file-load-error'] ??= __('filepond-loading-error-lable');
        $input['label-file-processing'] ??= __('filepond-processing-lable');
        $input['label-file-remove-error'] ??= __('filepond-removing-error-lable');

        return $input;
    }

    public function getAcceptedFileTypes($acceptedExtensions){
        $acceptedFileTypes = [];

        foreach($acceptedExtensions as $extension){
            $extension = strtolower($extension);
            if(!preg_match('/^\.(.)+$/', $extension, $matches)){
                $extension = '.'.$extension;
            }

            if(isset($this->acceptedExtensionMaps[$extension])){
                $acceptedFileTypes[] = $this->acceptedExtensionMaps[$extension];
            }

        }
        // dd($acceptedFileTypes);

        return implode(',', $acceptedFileTypes);
    }
}
