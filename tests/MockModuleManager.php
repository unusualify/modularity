<?php

namespace Unusualify\Modularous\Tests;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;

class MockModuleManager
{
    /**
     * Path to mock modules directory
     */
    protected static $mockModulesPath;

    /**
     * Initialize mock modules environment
     */
    public static function initialize()
    {
        self::$mockModulesPath = IsolatedTestModules::path();

        if (! self::$mockModulesPath) {
            throw new \Exception('Mock modules path not found: ' . IsolatedTestModules::sourcePath());
        }

        // Set modules path to mock modules
        Config::set('modules.paths.modules', self::$mockModulesPath);
    }

    /**
     * Get all mock modules
     */
    public static function all()
    {
        return Modularous::all();
    }

    /**
     * Get a specific mock module
     */
    public static function get($moduleName)
    {
        return Modularous::find($moduleName);
    }

    /**
     * Check if mock module exists
     */
    public static function has($moduleName)
    {
        return Modularous::has($moduleName);
    }

    /**
     * Get mock modules path
     */
    public static function getPath()
    {
        return self::$mockModulesPath;
    }

    /**
     * Get TestModule
     */
    public static function getTestModule()
    {
        return self::get('TestModule');
    }

    /**
     * Get SystemModule
     */
    public static function getSystemModule()
    {
        return self::get('SystemModule');
    }

    /**
     * Get module config
     */
    public static function getConfig($moduleName)
    {
        $module = self::get($moduleName);

        return $module ? $module->getConfig() : null;
    }

    /**
     * Get module entity
     */
    public static function getEntity($moduleName, $entityName)
    {
        $module = self::get($moduleName);
        if (! $module) {
            return null;
        }

        try {
            return $module->getModel($entityName);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get module repository
     */
    public static function getRepository($moduleName, $repositoryName)
    {
        $module = self::get($moduleName);
        if (! $module) {
            return null;
        }

        try {
            return $module->getRepository($repositoryName);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get module controller
     */
    public static function getController($moduleName, $controllerName)
    {
        $module = self::get($moduleName);
        if (! $module) {
            return null;
        }

        try {
            return $module->getController($controllerName);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * List available entities in module
     */
    public static function listEntities($moduleName)
    {
        $entitiesPath = self::$mockModulesPath . "/{$moduleName}/Entities";

        if (! is_dir($entitiesPath)) {
            return [];
        }

        $entities = [];
        foreach (scandir($entitiesPath) as $file) {
            if ($file !== '.' && $file !== '..' && str_ends_with($file, '.php')) {
                $entities[] = basename($file, '.php');
            }
        }

        return $entities;
    }

    /**
     * List available repositories in module
     */
    public static function listRepositories($moduleName)
    {
        $repositoriesPath = self::$mockModulesPath . "/{$moduleName}/Repositories";

        if (! is_dir($repositoriesPath)) {
            return [];
        }

        $repositories = [];
        foreach (scandir($repositoriesPath) as $file) {
            if ($file !== '.' && $file !== '..' && str_ends_with($file, '.php')) {
                $repositories[] = basename($file, '.php');
            }
        }

        return $repositories;
    }

    /**
     * List available controllers in module
     */
    public static function listControllers($moduleName)
    {
        $controllersPath = self::$mockModulesPath . "/{$moduleName}/Controllers";

        if (! is_dir($controllersPath)) {
            return [];
        }

        $controllers = [];
        foreach (scandir($controllersPath) as $file) {
            if ($file !== '.' && $file !== '..' && str_ends_with($file, '.php')) {
                $controllers[] = basename($file, '.php');
            }
        }

        return $controllers;
    }
}
