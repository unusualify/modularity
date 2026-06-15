<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Unusualify\Modularous\Services\Connector;

class RepeaterHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'autoIdGenerator' => true,
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'collapsible' => false,
        'translated' => false,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $originalType = $input['type'];
        $input['type'] = 'input-repeater';

        if ($originalType === 'repeater') {
            $input['root'] = 'default';
        } else {
            $input['root'] = $originalType;
        }

        if ($input['draggable'] ?? false) {
            $input['orderKey'] ??= 'position';
        }

        $input['singularLabel'] = isset($input['label']) ? Str::singular($input['label']) : null;

        $default_repeater_col = [
            'cols' => 12,
        ];
        $input['col'] = array_merge_recursive_preserve($default_repeater_col, $input['col'] ?? []);

        if (array_key_exists('schema', $input)) {
            $inputStudlyName = '';
            $inputSnakeName = '';

            if (isset($input['repository'])) {
                if (preg_match('/(\w+)Repository/', get_class_short_name($input['repository']), $matches)) {
                    // $relation_class = App::make($input['repository']);
                    $inputStudlyName = $matches[1];
                    $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                    // $inputCamelName = $this->getCamelCase($inputStudlyName);
                }
            } elseif (isset($input['model'])) {
                // if( preg_match( '/(\w+)/', get_class_short_name($input['model']), $matches)){
                //     dd($matches);
                //     $relation_class = App::make($input['model']);

                //     $inputStudlyName = $matches[1];
                //     $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                // }
            } elseif (isset($input['newConnector'])) {
                $connector = new Connector($input['newConnector']);
                $inputStudlyName = $connector->getRouteName();
                $inputSnakeName = $this->getSnakeCase($inputStudlyName);
            }

            foreach ($input['schema'] as $key => &$_input) {
                $_input['translated'] = $_input['translated'] ?? false;
                switch ($_input['type']) {
                    case 'select':
                    case 'combobox':
                    case 'autocomplete':
                        if ($inputSnakeName) {

                            if (preg_match("/{$inputSnakeName}_id/", $_input['name'])) { // it means foreign_id of pivot table
                                if (isset($input['repository'])) {
                                    $_input['repository'] ??= $input['repository'];
                                } elseif (isset($input['model'])) {
                                    $_input['model'] ??= $input['model'];
                                } elseif (isset($input['newConnector'])) {
                                    $_input['newConnector'] ??= $input['newConnector'];
                                }
                            } else {
                                $_input['items'] ??= [];
                            }

                            break;
                        }
                    default:
                        // code...
                        break;
                }
            }

            // $input['schema'] = $this->createFormSchema($input['schema']);
        }

        return $input;
    }
}
