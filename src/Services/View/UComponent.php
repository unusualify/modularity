<?php

namespace Unusualify\Modularity\Services\View;

use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use Unusualify\Modularity\Traits\ManageNames;

class UComponent extends Component
{
    use ManageNames;

    /**
     * The tag of the component
     *
     * @var string
     */
    public $tag;

    /**
     * The attributes of the component
     *
     * @var array
     */
    public $attributes = [];

    /**
     * The slots of the component
     *
     * @var array
     */
    public $slots = [];

    /**
     * The directives of the component
     *
     * @var array
     */
    public $directives = [];

    /**
     * The elements of the component
     *
     * @var array
     */
    public $elements = null;

    /**
     * The constructor of the component
     */
    public function __construct() {}

    /**
     * Make a new component
     *
     * @return self
     */
    public static function make(): self
    {
        return new self;
    }

    /**
     * Make a new component
     *
     * @return self
     */
    public function makeComponent($tag, $attributes = [], $elements = '', $slots = [], $directives = [])
    {
        return $this->setTag($tag)
            ->setAttributes($attributes)
            ->setElements($elements)
            ->setSlots($slots)
            ->setDirectives($directives);
    }

    /**
     * Set the tag of the component
     *
     * @param string $tag
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the attributes of the component
     *
     * @param array $attributes
     * @return self
     */
    public function setAttributes($attributes)
    {
        $attributes = $this->hydrateAttributes($attributes);

        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set the slots of the component
     *
     * @param array $slots
     * @return self
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * Set the directives of the component
     *
     * @param array $directives
     * @return self
     */
    public function setDirectives($directives)
    {
        $this->directives = $directives;

        return $this;
    }

    /**
     * Set the elements of the component
     *
     * @param array $elements
     * @return self
     */
    public function setElements($elements)
    {
        if ($elements !== '') {
            $this->elements = $elements;
        }

        return $this;
    }

    /**
     * Add a child to the component
     *
     * @param array $element
     * @return self
     */
    public function addChildren($element)
    {
        $oldElements = $this->elements;

        if (! is_array($oldElements)) {
            if (! empty($oldElements)) {
                $oldElements = [$oldElements];
            }
        } else if (is_array($oldElements) && Arr::isAssoc($oldElements)) {
            $oldElements = [$oldElements];
        }

        $newElement = [];
        if (is_string($element)) {
            $newElement = ___($element);
        } elseif (is_array($element)) {
            $newElement = $element;
        } elseif (get_class($element) === get_class($this)) {
            $newElement = $element->render();
        } else {
            $newElement = $element;
        }

        $this->elements = is_array($oldElements)
            ? array_merge($oldElements, $newElement)
            : $newElement;

        return $this;
    }

    /**
     * Add a slot to the component
     *
     * @param string $slotName
     * @param array $slotContent
     * @return self
     */
    public function addSlot($slotName, $slotContent)
    {
        $this->slots[$slotName] = $slotContent;

        return $this;
    }

    /**
     * Hydrate the attributes of the component
     *
     * @param array $attributes
     * @return array
     */
    public function hydrateAttributes($attributes)
    {
        if(!empty($attributes) && $this->tag === 'ue-table'){
            dd($attributes, $this);
        }

        return $attributes;
    }
    /**
     * Call a method
     *
     * @param string $method
     * @param array $args
     * @return self
     */
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

    /**
     * Call a static method
     *
     * @param string $method
     * @param array $args
     * @return self
     */
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

    /**
     * Render the component
     *
     * @return array
     */
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
