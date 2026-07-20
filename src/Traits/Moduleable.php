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

    public function getRouteName(): ?string
    {
        if ($this->routeName) {
            return $this->routeName;
        }

        if (preg_match('/(\w+)(?=(Request|Repository|Controller))/', get_class_short_name($this), $matches)) {
            $this->routeName = studlyName($matches[1]);

            return $this->routeName;
        }

        if (preg_match('/(\w+)\Entities/', get_class($this), $matches)) {
            $this->routeName = studlyName(get_class_short_name($this));

            return $this->routeName;
        }

        return $this->routeName;
    }

    /**
     * Get the permission prefix for the model.
     *
     * @return string
     */
    public function getPermissionPrefix(): string
    {
        return kebabCase($this->getRouteName());
    }

    /**
     * Get the permission name for the model.
     *
     * @param string $suffix
     * @return string
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
     */
    public function setRouteName(string $routeName): static
    {
        $this->routeName = $routeName;

        return $this;
    }
}
