<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;

class RepeaterHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'autoIdGenerator' => true
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-repeater';

        if( $input['draggable'] ?? false){
            $input['orderKey'] ??= 'position';
        }

        $input['singularLabel'] = isset($input['label']) ? \Illuminate\Support\Str::singular($input['label']) : null;

        $default_repeater_col = [
            'cols' => 12,
        ];
        $input['col'] = array_merge_recursive_preserve($default_repeater_col, $input['col'] ?? []);

        if(array_key_exists('schema', $input)){
            $inputStudlyName = '';
            $inputSnakeName = '';

            if(isset($input['repository'])){
                if( preg_match( '/(\w+)Repository/', get_class_short_name($input['repository']), $matches)){
                    $relation_class = App::make($input['repository']);
                    $inputStudlyName = $matches[1];
                    $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                    $inputCamelName = $this->getCamelCase($inputStudlyName);
                }
            } else if(isset($input['model'])){
                // if( preg_match( '/(\w+)/', get_class_short_name($input['model']), $matches)){
                //     dd($matches);
                //     $relation_class = App::make($input['model']);

                //     $inputStudlyName = $matches[1];
                //     $inputSnakeName = $this->getSnakeCase($inputStudlyName);
                // }
            }
            foreach ($input['schema'] as $key => &$_input) {
                $_input['translated'] = false;
                switch ($_input['type']) {
                    case 'select':
                    case 'combobox':
                    case 'autocomplete':
                        if($inputSnakeName){

                            if(preg_match("/{$inputSnakeName}_id/", $_input['name'])){ // it means foreign_id of pivot table
                                if(isset($input['repository'])){
                                    $_input['repository'] ??= $input['repository'];
                                } else if(isset($input['model'])){
                                    $_input['model'] ??= $input['model'];
                                }
                            }else {
                                $_input['items'] ??= [];
                            }
                            break;
                        }
                    default:
                        # code...
                        break;
                }
            }

            // $input['schema'] = $this->createFormSchema($input['schema']);
        }

        return $input;
    }
}
