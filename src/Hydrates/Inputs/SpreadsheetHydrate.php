<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class SpreadsheetHydrate extends InputHydrate
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
        'max-files' => null,

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

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-spreadsheet';

        $input['credits'] = false;

        $input['inputName'] = $input['name'] ?? 'spreadsheet';

        $input['max-files'] = $input['max'] ?? $input['max-files'];

        $input['label-idle'] ??= __('Drag & Drop your files or Browse');

        $input['label-invalid-field'] ??= __('filepon-invalid-field-label');
        $input['label-file-loading'] ??= __('filepond-loading-lable');
        $input['label-file-load-error'] ??= __('filepond-loading-error-lable');
        $input['label-file-processing'] ??= __('filepond-processing-lable');
        $input['label-file-remove-error'] ??= __('filepond-removing-error-lable');


        return $input;
    }
}
