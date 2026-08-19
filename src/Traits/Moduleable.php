<?php

namespace Unusualify\Modularous\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Repositories\Repository;

trait Moduleable
{
    /**
     * @var string|null
     */
    protected $moduleName = null;

    /**
     * @var string|null
     */
    protected $routeName = null;

    /**
     * @var string|null
     */
    protected $moduleRouteName = null;

    public function getModuleName(): ?string
    {
        if ($this->moduleName) {
            return $this->moduleName;
        }

        if (preg_match('/[M|m]{1}odules[\/\\\]([A-Za-z]+)[\/\\\]/', get_class($this), $matches)) {
            $this->moduleName = $matches[1];

            return $this->moduleName;
        }

        if (property_exists($this, 'repository') && $this->repository instanceof Repository) {
            $this->moduleName = class_basename($this->repository->getModel());

            return $this->moduleName;
        }

        if (property_exists($this, 'model') && $this->model instanceof Model) {
            $this->moduleName = class_basename($this->model);

            return $this->moduleName;
        }

        $this->moduleName = class_basename(static::class);

        return $this->moduleName;
    }

    public function getModuleRouteName(): ?string
    {
        if ($this->moduleRouteName) {
            return $this->moduleRouteName;
        }

        // Controllers historically only populate $routeName via setupRouteName().
        if ($this->routeName) {
            return $this->moduleRouteName = $this->routeName;
        }

        if (preg_match('/(\w+)(?=(Request|Repository|Controller))/', get_class_short_name($this), $matches)) {
            $this->moduleRouteName = studlyName($matches[1]);
            $this->routeName ??= $this->moduleRouteName;

            return $this->moduleRouteName;
        }

        if (preg_match('/(\w+)\Entities/', get_class($this), $matches)) {
            $this->moduleRouteName = studlyName(get_class_short_name($this));
            $this->routeName ??= $this->moduleRouteName;

            return $this->moduleRouteName;
        }

        return $this->moduleRouteName;
    }

    /**
     * @deprecated Use getModuleRouteName instead
     */
    public function getRouteName(): ?string
    {
        if ($this->routeName) {
            return $this->routeName;
        }

        if ($this->moduleRouteName) {
            return $this->routeName = $this->moduleRouteName;
        }

        if (preg_match('/(\w+)(?=(Request|Repository|Controller))/', get_class_short_name($this), $matches)) {
            $this->routeName = studlyName($matches[1]);
            $this->moduleRouteName ??= $this->routeName;

            return $this->routeName;
        }

        if (preg_match('/(\w+)\Entities/', get_class($this), $matches)) {
            $this->routeName = studlyName(get_class_short_name($this));
            $this->moduleRouteName ??= $this->routeName;

            return $this->routeName;
        }

        return $this->routeName;
    }

    /**
     * Get the permission prefix for the model.
     */
    public function getPermissionPrefix(): string
    {
        return kebabCase($this->getModuleRouteName());
    }

    /**
     * Get the permission name for the model.
     *
     * @param string $suffix
     *
     * @throws \InvalidArgumentException
     */
    public function getPermissionName($suffix): string
    {
        return "{$this->getPermissionPrefix()}_{$suffix}";
    }

    /**
     * @return $this
     */
    public function setModuleName(string $moduleName): static
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * @return $this
     * @deprecated Use setModuleRouteName instead
     */
    public function setRouteName(string $routeName): static
    {
        $this->routeName = $routeName;
        $this->moduleRouteName = $routeName;

        return $this;
    }

    /**
     * @return $this
     */
    public function setModuleRouteName(string $moduleRouteName): static
    {
        $this->moduleRouteName = $moduleRouteName;
        $this->routeName = $moduleRouteName;

        return $this;
    }
}
