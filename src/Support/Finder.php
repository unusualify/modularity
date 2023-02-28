<?php

namespace Unusual\CRM\Base\Support;

use Nwidart\Modules\Support\Migrations\SchemaParser as Parser;
use Unusual\CRM\Base\Traits\Namable;

use Composer\Autoload\ClassMapGenerator;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Finder
{
    use Namable;

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

        foreach (array_filter( glob( base_path( config('modules.namespace')).'/*'), 'is_dir') as $module_path) {

            if( !file_exists( $module_path.'/Entities') ) continue;

            foreach($this->getClasses( $module_path.'/Entities' ) as $class){
                // dd($module_path, $class);

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

        foreach($this->getAllModels() as $class){
            if( method_exists($class,'getTable') ){
                if( with(new $class())->getTable() == $table ){
                    $model_class = $class;
                    break;
                }
            }
        }

        if($model_class !== '') return $model_class;


        return false;
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

        foreach ($autoload as $className => $path) {
            // skip if we are not in the root namespace, ie App\, to ignore other vendor packages, of which there are a lot (dd($autoload) to see)
            try {
                if ( ! substr($className, 0, strlen($namespace)) === $namespace) {
                    continue;
                }
                // check if class is extending Model
                if (is_subclass_of($className, "Illuminate\Database\Eloquent\Model")) {
                    $models[] = $className;
                }
            } catch (\Throwable $th) {
                //throw $th;
            }

        }

        return collect($models);
    }


}
