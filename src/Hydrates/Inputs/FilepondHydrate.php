<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\Storage;

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
        'accepted-file-types' => [ // Acceptable file types - however not working well for now
            'image/*, file/*'
        ],

        // Multiple file upload functionalities
        'allow-multiple' => true,
        'max-files' => null,

        // Other Functionalities
        'allow-drop' => true,
        'allow-replace' => true, //only works when allowMultiple is false
        'allow-remove' => true,
        'allow-reorder' => false,
        'allow-process' => true,
        'allow-image-preview' => false,

        // Drag-Drop Properties
        'drop-on-page' => false, //FilePond will catch all files dropped on the webpage
        'drop-on-element' => true, //Require drop on the FilePond element itself to catch the file.
        'drop-validation' => false, //When enabled, files are validated before they are dropped. A file is not added when it's invalid.
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

        $input['endPoints'] = [
            'process' => route('admin.filepond.process'),
            'revert' => route('admin.filepond.delete'),
            'load' => 'http://' . unusualConfig('admin_app_url') . '/' . Storage::url('fileponds') . '/',
        ];

        // Custom Labels
        $input['label-idle'] ??= __('filepond-upload-label');
        $input['label-invalid-field'] ??= __('filepon-invalid-field-label');
        $input['label-file-loading'] ??= __('filepond-loading-lable');
        $input['label-file-load-error'] ??= __('filepond-loading-error-lable');
        $input['label-file-processing'] ??= __('filepond-processing-lable');
        $input['label-file-remove-error'] ??= __('filepond-removing-error-lable');

        return $input;
    }
}
