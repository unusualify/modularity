<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Module;
use Unusualify\Modularity\Traits\ManageNames;

abstract class InputHydrate
{
    use ManageNames;

    /**
     * İnput Schema array
     *
     *  [
     *      'type' => '${input-type}',
     *      'name' => '${input-name}',
     *      ...
     *  ]
     *
     * @var array
     */
    public $input = [];

    /**
     * İnput Schema array
     *
     *
     * @var Unusualify\Modularity\Module
     */
    public $module;

    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [];

    /**
     * Accepted extension maps
     *
     * @var array
     */
    public $acceptedExtensionMaps = [
        '.csv' => 'text/csv',
        '.doc' => 'application/msword',
        '.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        '.pdf' => 'application/pdf',
        '.pages' => 'application/x-iwork-pages-sffpages',
        '.numbers' => 'application/x-iwork-numbers-sffnumbers',
        '.key' => 'application/x-iwork-keynote-sffkey',
        '.ppt' => 'application/vnd.ms-powerpoint',
        '.pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        '.xls' => 'application/vnd.ms-excel',
        '.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        '.dbf' => 'application/dbf',
        '.geoJSon' => 'application/vnd.geo+json',
        '.gml' => 'application/gml+xml',
        '.kml' => 'application/vnd.google-earth.kml+xml',
        '.kmz' => 'application/vnd.google-earth.kmz',
        '.prj' => 'application/octet-stream',
        '.sbn' => 'application/octet-stream',
        '.sbx' => 'application/octet-stream',
        '.shp' => 'application/octet-stream',
        '.shpz' => 'application/octet-stream',
        '.shx' => 'application/octet-stream',
        '.wkt' => 'application/octet-stream',
        '.txt' => 'text/plain',
        '.rtf' => 'application/rtf',
        '.zip' => 'application/zip',
        '.rar' => 'application/x-rar-compressed',
        '.7z' => 'application/x-7z-compressed',
        '.tar' => 'application/x-tar',
        '.gz' => 'application/gzip',
        '.mp3' => 'audio/mpeg',
        '.wav' => 'audio/wav',
        '.mp4' => 'video/mp4',
        '.avi' => 'video/x-msvideo',
        '.mov' => 'video/quicktime',
        '.jpg' => 'image/jpeg',
        '.jpeg' => 'image/jpeg',
        '.png' => 'image/png',
        '.gif' => 'image/gif',
        '.svg' => 'image/svg+xml',
        '.xml' => 'application/xml',
        '.json' => 'application/json',
        '.html' => 'text/html',
        '.css' => 'text/css',
        '.js' => 'application/javascript',
        '.odt' => 'application/vnd.oasis.opendocument.text',
        '.ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        '.odp' => 'application/vnd.oasis.opendocument.presentation',
    ];

    /**
     * Create a new HydrateInput instance.
     */
    public function __construct(array $input, ?Module $module = null)
    {
        $this->input = $input;

        $this->module = $module;
    }

    /**
     * Set default values if not exists
     */
    public function setDefaults(): void
    {
        foreach ($this->requirements as $attribute => $defaultValue) {
            $this->input[$attribute] ??= $defaultValue;
        }
    }

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    abstract public function hydrate();

    /**
     * return hydrated input
     */
    public function render(): array
    {
        $this->setDefaults();

        $this->input = $this->hydrate();

        (! isset($this->input['skipRecords']) || ! $this->input['skipRecords']) && $this->input = $this->hydrateRecords();

        $this->input = $this->hydrateRules();

        $this->input = Arr::except($this->input, ['route', 'model', 'repository', 'cascades', 'connector']);

        return $this->input;
    }

    /**
     *  Set records wrt repository
     *
     * @return array
     */
    protected function hydrateRecords()
    {
        $input = $this->input;

        $noRecords = isset($input['noRecords']) && $input['noRecords'];

        if (isset($input['repository']) && ! $noRecords && ! App::runningInConsole()) {
            $args = explode(':', $input['repository']);

            $className = array_shift($args);
            $methodName = array_shift($args) ?? 'list';

            if (! @class_exists($className)) {
                return $input;
            }

            $repository = App::make($className);

            $params = Collection::make($args)->mapWithKeys(function ($arg) {
                [$name, $value] = explode('=', $arg);

                // return [$name => [$value]];
                return [$name => explode(',', $value)];
            })->toArray();

            $params = array_merge_recursive($params, ['with' => $this->getWiths()]);
            // dd($params, [$input['itemTitle'] ?? 'name', ...$this->getItemColumns()]);

            $items = call_user_func_array([$repository, $methodName], [
                ...($methodName == 'list' ? ['column' => [$input['itemTitle'] ?? 'name', ...$this->getItemColumns()]] : []),
                ...$params,
            ])->toArray();

            $input['items'] = $items;
            // $input =  Arr::except($input, ['route', 'model', 'repository']) + [
            //     'items' => $items
            // ];
            if (count($input['items']) > 0) {
                // if(!isset($input['itemTitle'])){
                //     dd($input);
                // }
                if (! isset($input['items'][0][$input['itemTitle']])) {
                    $input['itemTitle'] = array_keys(Arr::except($input['items'][0], [$input['itemValue']]))[0];
                }
            }
            $this->afterHydrateRecords($input);
        }

        return $input;
    }

    /**
     *  Handle input after records set
     *
     * @param array &$input
     * @return void
     */
    public function afterHydrateRecords(&$input) {}

    /**
     * Get withs to add to model's withs
     *
     * @return array
     */
    protected function getWiths()
    {
        $input = $this->input;

        $withs = [];

        if (isset($input['cascades'])) {
            $withs = $input['cascades'];
        }

        $withs = array_merge($withs, $this->withs());

        return $withs;
    }

    /**
     *  Withs defined on the input to add to model's withs
     */
    public function withs(): array
    {
        return [];
    }

    protected function getItemColumns()
    {
        $input = $this->input;

        $columns = [];

        if (isset($input['ext'])) {
            $extensionMethods = $input['ext'];
            if (is_string($input['ext'])) {
                $extensionMethods = explode('|', $input['ext']);
            }

            $columns = array_merge(collect($extensionMethods)->filter(function ($pattern) {
                $args = $pattern;
                if (is_string($pattern)) {
                    $pattern = trim($pattern);
                    $args = explode(':', $pattern);
                }

                return in_array($args[0], ['lock']);
            })
                ->map(function ($pattern) {
                    $args = $pattern;
                    if (is_string($pattern)) {
                        $pattern = trim($pattern);
                        $args = explode(':', $pattern);
                    }

                    return $args[1];
                })
                ->toArray(), $columns);
            // $items = $relation_class->list([$input['itemTitle'], ...$extensionColumnNames], $with)->toArray();
        }
        $columns = array_merge($columns, $this->itemColumns());

        return $columns;
    }

    public function itemColumns(): array
    {
        return [];
    }

    public function hydrateRules()
    {
        $input = $this->input;

        if (isset($input['rules']) && is_string($input['rules'])) {
            if (preg_match('/required/', $input['rules'])) {
                if (isset($input['class'])) {
                    $input['class'] .= ' required';
                } else {
                    $input['class'] = 'required';
                }
            }
        }

        return $input;
    }

    /**
     * Get accepted file types
     *
     * @param array $acceptedExtensions
     * @return string
     */
    public function getAcceptedFileTypes($acceptedExtensions)
    {
        $acceptedFileTypes = [];

        foreach ($acceptedExtensions as $extension) {
            $extension = mb_strtolower($extension);
            if (! preg_match('/^\.(.)+$/', $extension, $matches)) {
                $extension = '.' . $extension;
            }

            if (isset($this->acceptedExtensionMaps[$extension])) {
                $acceptedFileTypes[] = $this->acceptedExtensionMaps[$extension];
            }

        }
        // dd($acceptedFileTypes);

        return implode(',', $acceptedFileTypes);
    }
    /**
     * Handle magic method __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
