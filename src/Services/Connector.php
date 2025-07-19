<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Collection;
use Unusualify\Modularity\Exceptions\ModuleNotFoundException;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;

class Connector
{
    /**
     * @var string
     */
    private $firstLevelSeparator = '^';

    /**
     * @var string
     */
    private $secondLevelSeparator = '|';

    /**
     * @var string
     */
    private $thirdLevelSeparator = '->';

    /**
     * @var string
     */
    private $fourthLevelSeparator = '?';

    /**
     * @var string
     */
    private $fifthLevelSeparator = '&';

    /**
     * @var string
     */
    private $sixthLevelSeparator = '=';

    /**
     * @var array
     */
    private $parsed;

    /**
     * @var array
     */
    private $connector;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var Class
     */
    private $target;

    /**
     * @var array
     */
    private $events = [];

    /**
     * constructor
     *
     * @param string|array $connector
     */
    public function __construct($connector = null, protected $setKey = 'endpoint')
    {
        if ($connector) {
            $this->connector = $connector;

            if (is_string($connector)) {
                $this->parsed = $this->parseConnector($connector);
            } else {
                $this->parsed = $connector;
            }
        }
    }

    /**
     * Parse connector string into array
     *
     * @return array
     */
    private function parseConnector(string $connector)
    {
        // 'Package|PackageFeature^uri:tags'
        // 'User|User^repository:getCountForAll'
        // 'SystemUser|User^repository:listAll:with=roles,permissions'
        // 'Package&PackageRegion^repository:list:scopes=hasVendablePackage:appends=number_of_countries,number_of_package_languages'

        // 'Package|PackageRegion^repository->list?scopes=hasVendablePackage&appends=number_of_countries,number_of_package_languages';

        $exploded = explode($this->firstLevelSeparator, $connector); // ^

        $this->setModuleAndRoute($exploded[0]);

        $this->setEvents($exploded[1] ?? '');

        return $connector;
    }

    /**
     * Set the module and route
     */
    private function setModuleAndRoute(string $raw)
    {
        if ($raw === '') {
            throw new \Exception('Invalid connector ' . $this->connector);
        }

        $exploded = explode($this->secondLevelSeparator, $raw);

        $moduleName = $exploded[0];

        if ($moduleName === '') {
            throw ModuleNotFoundException::moduleMissing('Missing module name for connector ' . $this->connector);
        }

        if (! Modularity::hasModule($moduleName)) {
            throw ModuleNotFoundException::moduleNotFound("Module $moduleName not found for connector " . $this->connector);
        }

        $this->module = Modularity::find($moduleName);

        $routeName = $exploded[1] ?? $moduleName;

        if (! $this->module->hasRoute($routeName)) {
            throw ModuleNotFoundException::routeNotFound("Route $routeName not found for connector " . $this->connector);
        }

        $this->routeName = $routeName;

    }

    /**
     * Set the event
     */
    private function setEvents(string $raw)
    {
        $events = [];

        if ($raw !== '') {
            $methodables = explode($this->secondLevelSeparator, $raw); // |

            foreach ($methodables as $methodable) {

                $stop = false;
                $targets = explode($this->thirdLevelSeparator, $methodable); // ->
                $targetTypeKey = array_shift($targets);
                $methodName = '';
                $args = [$this->routeName];

                switch ($targetTypeKey) {
                    case 'uri':
                        $this->target = $this->module;

                        if (count($targets) > 0) {
                            $args[] = array_shift($targets);
                        } else {
                            $args[] = 'index';
                        }
                        $methodName = 'getRouteActionUri';
                        $stop = true;

                        $events[] = [
                            'name' => $methodName,
                            'args' => $args,
                        ];

                        break;
                    default:
                        $args = [];
                        $className = $this->module->getRouteClass($this->routeName, $targetTypeKey);

                        if (! class_exists($className)) {
                            throw new \Exception("Class {$className} not found for connector " . $this->connector);
                        }

                        $this->target = app($className);
                        $this->setKey = 'items';

                        break;
                }

                if (! $stop && count($targets) > 0) { // repository->list?scopes=hasVendablePackage&appends=number_of_countries,number_of_package_languages
                    foreach ($targets as $targetEventNotation) {
                        $targetEventNotationExploded = explode($this->fourthLevelSeparator, $targetEventNotation); // ?
                        $methodName = array_shift($targetEventNotationExploded);
                        $args = [];

                        if (count($targetEventNotationExploded) > 0) {
                            $targetEventArgs = explode($this->fifthLevelSeparator, $targetEventNotationExploded[0]); // &

                            $isOrderedArgs = null;
                            $isNamedArgs = null;
                            foreach ($targetEventArgs as $index => $targetEventArgsItem) {
                                $targetEventArgsItemExploded = explode($this->sixthLevelSeparator, $targetEventArgsItem); // =

                                $argKey = isset($targetEventArgsItemExploded[1]) ? $targetEventArgsItemExploded[0] : $index;
                                $parameter = $targetEventArgsItemExploded[1] ?? $targetEventArgsItemExploded[0];

                                if ($argKey !== $index) {
                                    if ($isOrderedArgs) {
                                        throw new \Exception('Both ordered and named arguments are not allowed at same time for connector ' . $this->connector);
                                    }
                                    $isNamedArgs = true;
                                } else {
                                    if ($isNamedArgs) {
                                        throw new \Exception('Both ordered and named arguments are not allowed at same time for connector ' . $this->connector);
                                    }
                                    $isOrderedArgs = true;
                                }

                                if (preg_match('/^\[.*\]$/', $parameter)) {
                                    $parameter = str_replace(['[', ']'], '', $parameter);

                                    // Split on commas that are not preceded by backslashes
                                    $values = preg_split('/(?<!\\\\),/', $parameter);

                                    // Unescape any remaining \, sequences
                                    $values = array_map(function($value) {
                                        return str_replace('\\,', ',', trim($value));
                                    }, $values);

                                    $args[$argKey] = $values;
                                } else {
                                    $args[$argKey] = $parameter;
                                }
                            }
                        }

                        $events[] = [
                            'name' => $methodName,
                            'args' => $args,
                        ];
                    }

                } else { // function
                    // $event['uri'] = [
                    //     'methods' => ['index' => []],
                    // ];
                }
            }
        } else {
            $events[] = [
                'name' => 'uri',
                'args' => ['index'],
            ];
        }

        $this->events = $events;
    }

    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Push an event to the events array
     *
     * @param array $event
     */
    public function pushEvent($event)
    {
        $this->events[] = $event;
    }

    /**
     * Get the events array
     *
     * @return array
     */
    public function unshiftEvent($event)
    {
        array_unshift($this->events, $event);
    }

    /**
     * Push multiple events to the events array
     *
     * @param array $events
     */
    public function pushEvents($events)
    {
        $this->events = array_merge($this->events, $events);
    }

    /**
     * Unshift multiple events to the events array
     *
     * @param array $events
     */
    public function unshiftEvents($events)
    {
        $this->events = array_merge($events, $this->events);
    }

    public function updateEventParameters($eventName, $parameters)
    {
        $events = Collection::make($this->events);

        $eventIndex = $events->search(function ($event) use ($eventName) {
            return $event['name'] === $eventName;
        });

        if ($eventIndex !== false) {
            $event = $events[$eventIndex];
            $this->events[$eventIndex]['args'] = array_merge($event['args'], $parameters);
        }

        // dd($this->events);
    }

    /**
     * Run the connector
     *
     * @param array|object $item
     * @param string|null $setKey
     * @return array|object
     */
    public function run(&$item = null, $setKey = null)
    {
        $target = $this->target;

        foreach ($this->events as $event) {
            $target = call_user_func_array([$target, $event['name']], [
                ...$event['args'],
            ]);
        }

        $setKey ??= $this->setKey;

        if (is_array($item)) {
            $item[$setKey] = $target;
        } elseif (is_object($item)) {
            $item->{$setKey} = $target;
        } elseif ($item instanceof Collection) {
            $item->{$setKey} = $target;
        }

        return $target;
    }
}
