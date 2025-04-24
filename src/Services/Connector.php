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
     * @param string|array $connector
     */
    public function __construct($connector = null, protected $setKey = 'endpoint')
    {
        if($connector) {
            $this->connector = $connector;

            if(is_string($connector)) {
                $this->parsed= $this->parseConnector($connector);
            } else {
                $this->parsed = $connector;
            }
        }
    }

    /**
     * Parse connector string into array
     * @param string $connector
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
     * @param string $raw
     */
    private function setModuleAndRoute(string $raw)
    {
        if($raw === '') {
            throw new \Exception('Invalid connector ' . $this->connector);
        }

        $exploded = explode($this->secondLevelSeparator, $raw);

        $moduleName = $exploded[0];

        if($moduleName === '') {
            throw ModuleNotFoundException::moduleMissing("Missing module name for connector " . $this->connector);
        }

        if(!Modularity::hasModule($moduleName)) {
            throw ModuleNotFoundException::moduleNotFound("Module $moduleName not found for connector " . $this->connector);
        }

        $this->module = Modularity::find($moduleName);

        $routeName = $exploded[1] ?? $moduleName;

        if(!$this->module->hasRoute($routeName)) {
            throw ModuleNotFoundException::routeNotFound("Route $routeName not found for connector " . $this->connector);
        }

        $this->routeName = $routeName;

    }

    /**
     * Set the event
     * @param string $raw
     */
    private function setEvents(string $raw)
    {
        $events = [];

        if($raw !== '') {
            $methodables = explode($this->secondLevelSeparator, $raw); // |

            foreach($methodables as $methodable) {

                $targets = explode($this->thirdLevelSeparator, $methodable); // ->

                if(count($targets) > 1) { // repository->list?scopes=hasVendablePackage&appends=number_of_countries,number_of_package_languages
                    $targetTypeKey = array_shift($targets);
                    $targetEventNotation = array_shift($targets);

                    $targetEventNotationExploded = explode($this->fourthLevelSeparator, $targetEventNotation); // ?

                    $methodName = array_shift($targetEventNotationExploded);
                    $args = [];

                    $stop = false;
                    switch ($targetTypeKey) {
                        case 'uri':
                            $this->target = $this->module;

                            $args = [$this->routeName, $methodName];
                            $methodName = 'getRouteActionUri';
                            $stop = true;

                            break;
                        default:
                            $className = $this->module->getRouteClass($this->routeName, $targetTypeKey);

                            if(!class_exists($className)) {
                                throw new \Exception("Class {$className} not found for connector " . $this->connector);
                            }

                            $this->target = app($className);

                            $this->setKey = 'items';
                            break;
                    }


                    if(!$stop){

                        if(count($targetEventNotationExploded) > 0) {
                            $targetEventArgs = explode($this->fifthLevelSeparator, $targetEventNotationExploded[0]); // &

                            $isOrderedArgs = null;
                            $isNamedArgs = null;
                            foreach($targetEventArgs as $index => $targetEventArgsItem) {
                                $targetEventArgsItemExploded = explode($this->sixthLevelSeparator, $targetEventArgsItem); // =

                                $argKey = isset($targetEventArgsItemExploded[1]) ? $targetEventArgsItemExploded[0] :  $index;
                                $parameter = $targetEventArgsItemExploded[1] ?? $targetEventArgsItemExploded[0];

                                if($argKey !== $index) {
                                    if($isOrderedArgs){
                                        throw new \Exception("Both ordered and named arguments are not allowed at same time for connector " . $this->connector);
                                    }
                                    $isNamedArgs = true;
                                } else {
                                    if($isNamedArgs){
                                        throw new \Exception("Both ordered and named arguments are not allowed at same time for connector " . $this->connector);
                                    }
                                    $isOrderedArgs = true;
                                }

                                if(preg_match('/^\[.*\]$/', $parameter)) {
                                    $parameter = str_replace(['[', ']'], '', $parameter);
                                    $values = explode(',', $parameter);
                                    $args[$argKey] = $values;
                                } else {
                                    $args[$argKey] = $parameter;
                                }
                            }

                        } else {
                            dd($targetEventNotationExploded, $methodName);
                            $args = [$targetEventNotation];

                            dd($args);
                        }
                    }


                    $events[] = [
                        'name' => $methodName,
                        'args' => $args,
                    ];


                }else { // function
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
     * @param array $event
     */
    public function pushEvent($event)
    {
        $this->events[] = $event;
    }

    /**
     * Get the events array
     * @return array
     */
    public function unshiftEvent($event)
    {
        array_unshift($this->events, $event);
    }

    /**
     * Push multiple events to the events array
     * @param array $events
     */
    public function pushEvents($events)
    {
        $this->events = array_merge($this->events, $events);
    }

    /**
     * Unshift multiple events to the events array
     * @param array $events
     */
    public function unshiftEvents($events)
    {
        $this->events = array_merge($events, $this->events);
    }

    /**
     * Run the connector
     * @param array|object $item
     * @param string|null $setKey
     * @return array|object
     */
    public function run(&$item = null, $setKey = null)
    {
        $target = $this->target;

        foreach($this->events as $event) {
            $target = call_user_func_array([$target, $event['name']], [
                ...$event['args'],
            ]);
        }

        $setKey ??= $this->setKey;

        if(is_array($item)) {
            $item[$setKey] = $target;
        } else if(is_object($item)) {
            $item->{$setKey} = $target;
        } else if($item instanceof Collection) {
            $item->{$setKey} = $target;
        }

        return $target;
    }

}