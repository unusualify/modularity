<?php

namespace Unusualify\Modularity\View;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ModularityWidget extends Component
{
    /**
     * The widget alias
     *
     * @var string
     */
    protected $widgetAlias;

    /**
     * The widget default config
     *
     * @var array
     */
    protected $widgetDefaultConfig = [
        'tag' => 'v-card',
        'widgetTag' => 'v-col',
        'widgetCol' => [
            'cols' => 12,
        ],
        'widgetAttributes' => [
            'class' => 'h-50 overflow-y-auto',
        ],
        'widgetSlots' => [],
    ];

    /**
     * The widget tag
     *
     * @var string
     */
    public $widgetTag;

    /**
     * The widget col
     *
     * @var array
     */
    public $widgetCol;

    /**
     * The widget attributes
     *
     * @var array
     */
    public $widgetAttributes;

    /**
     * The widget slots
     *
     * @var array
     */
    public $widgetSlots;

    /**
     * Use the widget config on modularity.widgets.{$widgetAlias}
     *
     * @var bool
     */
    public $widgetConfigUsable = false;

    public function __construct()
    {
        parent::__construct();

        $this->widgetAlias = Str::kebab(preg_replace('/Widget$/', '', get_class_short_name(static::class)));

        if (! isset($this->tag)) {
            if (static::class !== self::class) {
                $this->tag = $this->widgetAlias;
            } else {
                $this->tag = $this->widgetDefaultConfig['tag'];
            }
        }

        if (count($this->attributes) > 0) {
            $this->attributes = $this->hydrateAttributes($this->attributes);
        }
    }

    /**
     * Create a widget from a template
     *
     * @return static
     */
    public static function fromWidgetTemplate(string $templateName, array $customAttributes = [])
    {
        $instance = new static;

        $widgetConfig = static::getWidgetTemplate($templateName);

        return $instance->makeComponent(
            $widgetConfig['tag'] ?? 'v-col',
            $widgetConfig
        );
    }

    /**
     * Get a widget template by name
     *
     * @param string $name
     * @return array|null
     */
    protected static function getWidgetTemplate(string $widgetAlias)
    {
        $widgetConfig = Config::get('modularity.widgets.' . $widgetAlias, null);

        if (! $widgetConfig) {
            throw new \Exception('Widget template not found');
        }

        return $widgetConfig;
    }

    public function setWidgetCol(array $widgetCol)
    {
        $this->widgetCol = $widgetCol;

        return $this;
    }

    public function setWidgetAttributes(array $widgetAttributes)
    {
        $this->widgetAttributes = $widgetAttributes;

        return $this;
    }

    public function setWidgetSlots(array $widgetSlots)
    {
        $this->widgetSlots = $widgetSlots;

        return $this;
    }

    /**
     * Use the widget config on modularity.widgets.{$widgetAlias}
     *
     * @return static
     */
    public function useWidgetConfig(bool $useWidgetConfig = true)
    {
        $this->widgetConfigUsable = $useWidgetConfig;

        return $this;
    }

    public function render()
    {
        $componentConfig = Config::get('modularity.widgets.' . $this->widgetAlias, []);

        $component = new Component;

        $component = $component->makeComponent(
            tag: $this->tag,
            attributes: array_merge_recursive_preserve(
                $this->widgetConfigUsable ? $this->hydrateAttributes($componentConfig['attributes'] ?? []) : [],
                $this->attributes ?? []
            ),
            elements: $this->elements,
            slots: array_merge(
                $this->widgetConfigUsable ? $componentConfig['slots'] ?? [] : [],
                $this->slots ?? []
            )
        );

        return [
            'tag' => $this->widgetTag,
            'attributes' => array_merge_recursive_preserve(
                $this->widgetConfigUsable ? $this->widgetDefaultConfig['widgetAttributes'] ?? [] : [],
                [...($this->widgetCol ?? [])],
                ($this->widgetAttributes ?? [])
            ),
            'slots' => array_merge(
                $this->widgetConfigUsable ? $this->widgetDefaultConfig['widgetSlots'] ?? [] : [],
                $this->widgetSlots ?? []
            ),
            'elements' => [$component->render()],
        ];
    }
}
