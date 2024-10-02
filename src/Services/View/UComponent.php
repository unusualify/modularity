<?php

namespace Unusualify\Modularity\Services\View;

use BadMethodCallException;
use Illuminate\View\Component;
use Unusualify\Modularity\Traits\ManageNames;

class UComponent extends Component
{
    use ManageNames;

    public $tag;

    public $elements;

    public $attributes = [];

    public $slots = [];

    public $directives = [];

    /********************* */

    public static function make(): self
    {
        return new self;
    }

    public function makeComponent($tag, $attributes = [], $elements = '', $slots = [], $directives = [])
    {
        return $this->setTag($tag)
            ->setAttributes($attributes)
            ->setElements($elements)
            ->setSlots($slots)
            ->setDirectives($directives);
    }

    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    public function setDirectives($directives)
    {
        $this->directives = $directives;

        return $this;
    }

    public function setElements($elements)
    {
        if ($elements !== '') {
            $this->elements = $elements;
        }

        return $this;
    }

    public function addChildren($element)
    {
        if (is_string($element)) {
            $this->elements = ___($element);
        } elseif (is_array($element)) {
            if (! is_array($this->elements)) {
                $this->elements = [];
            }
            $this->elements[] = $element;
        } elseif (get_class($element) === get_class($this)) {
            if (! is_array($this->elements)) {
                $this->elements = [];
            }
            $this->elements[] = $element->render();
        } else {
            $this->elements = $element;
        }

        return $this;
    }

    public function __call($method, $args)
    {

        if (preg_match('/make([V|Ue][A-Za-z]+)/', $method, $match)) {
            $tag = $this->getKebabCase($match[1]);

            return $this->makeComponent($tag, ...$args);
        }
        if (preg_match('/addChildren([V|Ue][A-Za-z]+)/', $method, $match)) {
            $tag = $this->getKebabCase($match[1]);

            return $this->addChildren($tag, ...$args);
        }

        if (! in_array($method, get_class_methods($this))) {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s->%s()', static::class, $method
            ));
        }
    }

    public static function __callStatic($method, $args)
    {
        $instance = new static;

        if (preg_match('/make([V|Ue][A-Za-z]{1,20}|Template|Div)/', $method, $match)) {
            $tag = $instance->getKebabCase($match[1]);

            return $instance->makeComponent($tag, ...$args);
        }

        if (! in_array($method, get_class_methods($instance))) {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()', static::class, $method
            ));
        }
    }

    public function render()
    {
        return [
            'tag' => $this->tag,
            'attributes' => $this->attributes,
            'slots' => $this->slots,
            'directives' => $this->directives,
            ...($this->elements ? ['elements' => $this->elements] : []),
        ];
    }
}
