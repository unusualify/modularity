<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Exception;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

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

        foreach ($input['sheet_columns'] as $key => $value) {
            if(preg_match('/[şİıöüÇçĞğ]/u', $value)) {
                throw new Exception("Can't use Turkish special characters in: " . $value);
            }
            $input['sheet_columns'][$key] = mb_strtolower($value);
        }
        $input['example_file'] = $this->generateExampleFile($input);
        return $input;
    }

    protected function generateExampleFile($input)
    {
        // Retrieve the model type information.
        $modelType = init_connector($input['connector']);
        $modelType = $modelType['module']->getRouteClass($modelType['route'], 'model');

        // Build a unique filename and path.
        $columnsPart = implode('_', $input['sheet_columns']);
        $filename    = "Spreadsheet_{$modelType}_{$input['name']}_{$columnsPart}.xlsx";
        $path        = 'spreadsheet_examples/' . $filename;

        // Use the path as the cache key (since it uniquely identifies the file).
        $cacheKey = $path;

        // Use Laravel's cache to avoid regenerating the file if it already exists.
        // The closure passed to Cache::remember will only run if the cache key is not set.
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDays(7), function () use ($input, $path) {
            // Initialize Faker (this code runs only if the file is not cached).
            $faker  = \Faker\Factory::create();

            // Create a header row using the sheet columns.
            $header = $input['sheet_columns'];

            // Generate a sample data row based on the header columns using switch-case.
            $dataRow = [];
            foreach ($input['sheet_columns'] as $column) {
                switch (true) {
                    case preg_match('/(url|link)/i', $column):
                        $dataRow[] = $faker->url;
                        break;

                    case preg_match('/id/i', $column):
                        $dataRow[] = $faker->randomNumber();
                        break;

                    case preg_match('/name/i', $column):
                        $dataRow[] = $faker->name;
                        break;

                    case preg_match('/description/i', $column):
                        $dataRow[] = $faker->paragraph;
                        break;

                    case preg_match('/(number|integer)/i', $column):
                        $dataRow[] = $faker->numberBetween(1, 100);
                        break;

                    default:
                        $dataRow[] = $faker->word;
                        break;
                }
            }

            // Combine the header and data row into one data array.
            $data = [
                $header,
                $dataRow,
            ];

            // Create an anonymous export class implementing FromArray.
            $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                private $data;

                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }
            };

            // Check if the file already exists on disk.
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            }

            // Store the Excel file on the 'public' disk.
            $stored = \Maatwebsite\Excel\Facades\Excel::store($export, $path, 'public');

            if ($stored) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            }

            throw new \Exception("Couldn't store the file; something went wrong.");
        });
    }

}
