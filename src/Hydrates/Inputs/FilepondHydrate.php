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
        'allowMultiple' => true,
        'max-files' => 2,

        // Other Functionalities
        'allowDrop' => true,
        'allowReplace' => true, // only works when allowMultiple is false
        'allowRemove' => true,
        'allowReorder' => false,
        'allowProcess' => true,
        'allowImagePreview' => false,

        // Drag-Drop Properties
        'dropOnPage' => false, // FilePond will catch all files dropped on the webpage
        'dropOnElement' => true, // Require drop on the FilePond element itself to catch the file.
        'dropValidation' => false, // When enabled, files are validated before they are dropped. A file is not added when it's invalid.

        // File Size Validation
        'allowFileSizeValidation' => true,
        'maxFileSize' => '5MB', // 5MB
        'minFileSize' => '1KB', // 1KB
        'maxTotalFileSize' => null,
        'labelMaxFileSize' => 'Maximum file size is {filesize}',
        'labelMaxFileSizeExceeded' => 'File is too large',
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

        if (isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])) {
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
}
