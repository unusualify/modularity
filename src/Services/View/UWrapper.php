<?php

namespace Unusualify\Modularity\Services\View;

use BadMethodCallException;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Unusualify\Modularity\Traits\ManageNames;
use Stringable;

class UWrapper
{
    use ManageNames;

    /********************* */

    public static function make(): self
    {
        return new self();
    }

    public static function makeGridSection($elements, $attributes = []){
        $row = UComponent::makeVRow()
            ->setAttributes(array_merge_recursive_preserve([
                'class' => '',
                // 'noGutters' => true
            ], $attributes));
        foreach ($elements as $key => $element) {
            $col = UComponent::makeVCol();

            $col_attributes = ['class' => 'd-flex', 'cols' => 12, 'lg' => 6];

            if(is_array($element)){
                $contents = $element;
                if(isset($element['content']) && is_array($element['content'])){
                    $col_attributes = array_merge_recursive_preserve($col_attributes, $element['parent_attributes']);

                    $contents = $element['content'];
                }

                if(count($contents) > 1){
                    $div = UComponent::makeDiv();
                    foreach($contents as $component){
                        $div->addChildren($component);
                    }
                    $col->addChildren($div);

                }else {
                    $col->addChildren($contents[0]);
                }

            }else if(get_class($element) === 'Unusualify\Modularity\Services\View\UComponent' ){
                $col->addChildren($element);
            }

            $col->setAttributes($col_attributes);

            $row->addChildren($col);
        }

        return $row->render();
    }

    public static function makeProfileWrapper($elements, $attributes = []){

    }

    public static function makeFormWrapper($forms)
    {
        return static::makeGridSection(
            Collection::make($forms)->map(function($form, $i){
                return UComponent::makeUeForm()
                    ->setAttributes($form);
            })->toArray()
        );
    }

    public function __call($method, $args) {

        if(preg_match('/make([V|Ue][A-Za-z]+)/', $method, $match)){
            $tag = $this->getKebabCase($match[1]);

            return $this->makeComponent($tag, ...$args);
        }
        if(preg_match('/addChildren([V|Ue][A-Za-z]+)/', $method, $match)){
            $tag = $this->getKebabCase($match[1]);

            return $this->addChildren($tag, ...$args);
        }

        if (!in_array($method, get_class_methods($this))) {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s->%s()', static::class, $method
            ));
        }
    }

    public static function __callStatic($method, $args) {
        $instance = new static;

        if(preg_match('/make([V|Ue][A-Za-z]+)/', $method, $match)){
            $tag = $instance->getKebabCase($match[1]);

            return $instance->makeComponent($tag, ...$args);
        }

        if (!in_array($method, get_class_methods($instance))) {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }
    }

}
