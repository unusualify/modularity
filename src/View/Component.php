<?php

namespace Unusualify\Modularity\View;

use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Component as LaravelComponent;
use Unusualify\Modularity\Traits\ManageNames;

class Component extends LaravelComponent
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

    protected $attributesHydrated = false;

    /**
     * The constructor of the component
     */
    public function __construct() {}

    public static function create($config)
    {
        if (isset($config['widgetAlias'])) {
            $config = array_merge_recursive_preserve(
                Config::get('modularity.widgets.' . $config['widgetAlias'], []),
                array_except($config, ['widgetAlias'])
            );
        }

        if (! (isset($config['widget']) || isset($config['component']) || isset($config['tag']))) {
            throw new \Exception('Widget, component or tag is required for component creation');
        }

        $component = new static;

        if (isset($config['widget'])) {
            $widgetClass = 'Unusualify\\Modularity\\View\\Widgets\\' . $config['widget'];
            if (! class_exists($widgetClass)) {
                throw new \Exception('Widget class ' . $widgetClass . ' does not exist');
            }

            $widget = $widgetClass::make();

            $alias = $config['widgetAlias'] ?? null;

            $component = $widget->useWidgetConfig()
                ->mergeSlots($config['slots'] ?? [])
                ->setWidgetCol(array_merge_recursive_preserve(
                    $widget->widgetCol ?? [],
                    $config['widgetCol'] ?? [],
                ))
                ->setWidgetAttributes(array_merge_recursive_preserve(
                    $widget->widgetAttributes ?? [],
                    $config['widgetAttributes'] ?? [],
                ))
                ->setWidgetSlots(array_merge_recursive_preserve(
                    $widget->widgetSlots ?? [],
                    $config['widgetSlots'] ?? [],
                ));


            if ($alias) {
                $component->setWidgetAlias($alias);
            }

            $component->mergeAttributes($config['attributes'] ?? []);

        } elseif (isset($config['component'])) {
            $component->setComponent($config['component'])
                ->mergeAttributes($config['attributes'] ?? []);
        } elseif (isset($config['tag'])) {
            $component->setTag($config['tag'])
                ->mergeAttributes($config['attributes'] ?? []);
        }

        return $component->render();
    }

    /**
     * Make a new component
     *
     * @return self
     */
    public static function make(): static
    {
        return new static;
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

        $this->attributesHydrated = true;

        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Merge attributes of the component
     *
     * @param array $attributes
     * @return self
     */
    public function mergeAttributes($attributes)
    {
        $this->attributes = $this->hydrateAttributes(array_merge_recursive_preserve(
            $this->attributes,
            $attributes
        ));

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
     * Merge slots of the component
     *
     * @param array $slots
     * @return self
     */
    public function mergeSlots($slots)
    {
        $this->slots = array_merge($this->slots, $slots);

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
        $wasIsAssoc = false;

        if (! is_array($oldElements)) {
            if (! empty($oldElements)) {
                $oldElements = [$oldElements];
            }
        } elseif (is_array($oldElements) && Arr::isAssoc($oldElements)) {
            $oldElements = [$oldElements];
            $wasIsAssoc = true;
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

        if (is_array($oldElements)) {
            $oldElements[] = $newElement;

        }

        $this->elements = is_array($oldElements)
            ? $oldElements
            : [$newElement];

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
        if (isset($attributes['connector'])) {
            $connectorInfo = find_module_and_route($attributes['connector']);
            $attributes['_module'] = $connectorInfo['module'];
            $attributes['_routeName'] = $connectorInfo['route'];
        }

        return $attributes;
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

    /**
     * Convert the component to an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->render();
    }

    /**
     * Convert the component to a JSON string
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
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
}
