<?php

namespace Unusualify\Modularity\Services\View;


class UWidget extends UComponent {

    public function setAttributes($attributes = [])
    {
        // $this->attributes = $attributes;
        $this->setWidgetAttributes($attributes);

        return $this;
    }

    public function setWidgetAttributes($attributes){
        //Get default attributes
        $deafultAttributes = [];
        $originalTag = $this->tag;
        $originalAttributes = $attributes;
        if(isset($attributes['component']) && $this->tag == 'v-col'){
            $originalTag =  $attributes['component'];
            $deafultAttributes = config('widgets.'. str_replace('ue-','',$originalTag));
        }
        // dd($deafultAttributes);
        // dd($this->tag, $this->attributes);
        // dd($this->tag);
        // $originalTag = str_replace('ue-','',$this->tag);
        // $originalAttributes = $this->attributes;
        // dd($attributes, $originalAttributes, $originalTag, $this->tag);
        if(isset($this->attributes['col'])){
            // dd('here', $this->tag);
            // if($this->tag == 'v-col')
            $this->attributes = $this->attributes['col'];
            // dd($this);
            // dd($this->attributes);
            // $col_attributes = $this->attributes['col'];
            // $this->attributes = $this->attributes['attributes'];
            // $col = UComponent::makeVCol();
            // dd($this);
        }elseif (!str_contains($this->tag, 'col')){
            // dd('here');
            // dd($this->attributes, $originalAttributes, $originalTag);
            // dd('here');
            $this->attributes = array_merge($originalAttributes, $this->attributes);
        }
        // dd('here');
        $methodName = 'set' . str_replace('ue-','',$originalTag) . 'Attributes';
        // dd($methodName);
        if(method_exists($this, $methodName)){
            // $test = $this->$methodName($attributes);
            // dd($test);
            // dd('here2');
            $this->addChildren($this->$methodName($attributes));
            // dd($col);
            return $this;
        }

        // else
        //     dd("This method doesn't exist: ". $methodName);
    }

    protected function setTableAttributes($attributes){

        if(isset($attributes['connector'])){
            // dd('here');
            $data = init_connector($attributes['connector']);
            // dd($data);
            $moduleAttributes = [
                'name' => '',
                'customTitle' => '',
            ];
            $data['items'] = $data['items']->toArray();
            $tableAttributes = array_merge($attributes, $moduleAttributes, $data);

            unset($tableAttributes['connector']);
            unset($tableAttributes['col']);
            // dd($this);
            // dd($attributes, $this->tag);

            // dd($this->attributes ?? [], $attributes);
            // $this->attributes comes as null because of v-col initialization check the side effects
            // dd($this->tag);
            if(str_contains($this->tag, 'col')){
                // dd($this->attributes);
                $table = new UComponent();

                $testAttributes = array_merge(
                    $tableAttributes['attributes'],
                    [
                        'items' => $tableAttributes['items'],
                        'route' => $tableAttributes['route'],
                        'repository' => $tableAttributes['repository'],
                        'module' => $tableAttributes['module'],
                    ]
                );
                // dd($tableAttributes, $testAttributes);
                $table = $table->makeComponent($attributes['component'], array_merge($this->attributes ?? [], $testAttributes));
                // dd('here');
                // $this->attributes = array_merge($this->attributes ?? [], $attributes);
                // return $table;
                // dd($this, $table);
            }
            else{
                $this->attributes = null;
            }
            // dd($table);
            return $table;
        }
        // dd($this);
        // dd($attributes);
        return $attributes;
    }

    // UWidget::makeTable()->setAttributes();


}
