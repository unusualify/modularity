<?php

namespace Unusualify\Modularity\Support;

use Unusualify\Modularity\Traits\ManageNames;
use Composer\Autoload\ClassMapGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

class Finder
{
    use ManageNames;

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct()
    {

    }

    public function getModel($table)
    {
        $model_class = '';
        // dd( array_filter( glob( base_path( config('modules.namespace')).'/*'), 'is_dir') );

        foreach (array_filter( glob( config('modules.paths.modules').'/*'), 'is_dir') as $module_path) {

            if( !file_exists( $module_path.'/Entities') ) continue;

            foreach($this->getClasses( $module_path.'/Entities' ) as $class){
                if( method_exists($class,'getTable') ){
                    if( with(new $class())->getTable() == $table ){
                        $model_class = $class;
                        break 2;
                    }
                }
            }
        }

        if($model_class !== '') return $model_class;

        foreach($this->getClasses( app_path('Models')) as $class){
            if( method_exists($class,'getTable') ){
                if( with(new $class())->getTable() == $table ){
                    $model_class = $class;
                    break;
                }
            }
        }

        if($model_class !== '') return $model_class;

        // foreach($this->getAllModels() as $class){
        //     dd($class);
        //     if( method_exists($class,'getTable') ){
        //         if( with(new $class())->getTable() == $table ){
        //             $model_class = $class;
        //             break;
        //         }
        //     }
        // }

        // if($model_class !== '') return $model_class;


        return false;
    }

    public function getRouteModel($routeName, $asClass = false)
    {
        $class = '';

        // dd(
        //     Modularity::allEnabled(),
        //     glob( base_path( config('modules.namespace')).'/*'),
        //     config('modules.scan')
        // );

        foreach ( Modularity::allEnabled() as $key => $module) {
            $entityPath =  $module->getDirectoryPath('Entities');
            if( !file_exists( $entityPath ) ) continue;

            foreach($this->getClasses( $entityPath ) as $_class){
                if(get_class_short_name(App::make($_class)) === $this->getStudlyName($routeName)){
                    $class = $_class;
                    break 2;
                }
            }
        }


        if($class !== '') return $asClass ? App::make($class) :  $class;


        return false;
    }

    public function getRepository($table)
    {
        $class = '';

        foreach (array_filter( glob( config('modules.paths.modules').'/*'), 'is_dir') as $module_path) {

            if( !file_exists( $module_path.'/Repositories') ) continue;

            foreach($this->getClasses( $module_path.'/Repositories' ) as $_class){
                // dd($module_path, $class);
                if( App::make($_class)->getTable() == $table ){
                    $class = $_class;
                    break 2;
                }
            }
        }

        if($class !== '') return $class;

        $repositoryPath = app_path('Repositories');

        if(file_exists($repositoryPath)){
            foreach($this->getClasses( app_path('Repositories')) as $_class){
                if( method_exists($_class,'getTable') ){
                    if( with(new $_class())->getTable() == $table ){
                        $class = $class;
                        break;
                    }
                }
            }
        }

        if($class !== '') return $class;

        return false;
    }

    public function getRouteRepository($routeName, $asClass = false)
    {
        $class = '';

        foreach ( Modularity::allEnabled() as $key => $module) {
            $path =  $module->getDirectoryPath('Repositories');
            if( !file_exists( $path ) ) continue;
            foreach($this->getClasses( $path ) as $_class){
                if(get_class_short_name(App::make($_class)) === $this->getStudlyName($routeName). 'Repository'){
                    $class = $_class;
                    break 2;
                }
            }
        }

        if($class !== '') return $asClass ? App::make($class) : $class;


        return false;
    }

    public function getPossibleModels($routeName) :array
    {
        return $this->getAllModels()->filter(fn($model) => get_class_short_name($model) == $routeName)->values()->toArray();
    }

    public function getClasses($path)
    {
        $classes = [];

        foreach (ClassMapGenerator::createMap($path) as $class => $file)
        {
            $classes[] = $class;
        }

        return $classes;
    }

    public function getAllModels(): Collection
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        // get the root namespace defined for the app
        $namespace = key($composer['autoload']['psr-4']);
        // load classes composer knows about
        $autoload = include base_path('/vendor/composer/autoload_classmap.php');
        $models = [];
        // dd($autoload);
        foreach ($autoload as $className => $path) {
            // skip if we are not in the root namespace, ie App\, to ignore other vendor packages, of which there are a lot (dd($autoload) to see)
            try {
                if ( ! substr($className, 0, strlen($namespace)) === $namespace) {
                    continue;
                }
                // check if class is extending Model
                if( !preg_match('/vendor\/unusualify\//', $path) && preg_match('/vendor\//', $path))
                    continue;

                $reflection = new \ReflectionClass($className);
                // dd(
                //     // get_class_methods($reflection),

                //     $reflection->getName(),
                //     $reflection->isUserDefined(),
                //     $reflection->isTrait(),
                //     $reflection->isAbstract(),
                //     $reflection->isSubclassOf("Illuminate\Database\Eloquent\Model"),
                //     // $reflection->getParentClass(),
                // );
                if(
                    $reflection->isUserDefined()
                    && !$reflection->isTrait()
                    && !$reflection->isAbstract()
                    && $reflection->isSubclassOf("Illuminate\Database\Eloquent\Model")
                ){
                    // dd($reflection->getName());
                    $models[] = $className;
                }
                // if (is_subclass_of($className, "Illuminate\Database\Eloquent\Model")) {
                // }
            } catch (\Throwable $th) {
                //throw $th;
            }

        }

        return collect($models);
    }


}
