<?php

namespace Unusualify\Modularous\Traits;

trait Traitify
{
    /**
     * Get the methods for method name and current trait name.
     *
     * {methodName}{traitName}
     *
     * @return string[]
     */
    protected function traitsMethods(?string $method = null)
    {
        $method = $method ?? debug_backtrace()[1]['function'];

        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $methods = array_map(function (string $trait) use ($method) {
            // if $methodName is snake_case, combining it and make it snake_case
            if(str_contains($method, '_')) {
                return $method . '_' . snakeCase($trait);
            } 
            return $method . $trait;
        }, $uniqueTraits);

        return array_filter($methods, function (string $method) {
            return method_exists(get_called_class(), $method);
        });
    }

    protected static function staticTraitsMethods(?string $method = null)
    {
        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $methods = array_map(function (string $trait) use ($method) {
            return $method . $trait;
        }, $uniqueTraits);

        return array_filter($methods, function (string $method) {
            return method_exists(get_called_class(), $method);
        });
    }

    /**
     * Get the properties for property name.
     *
     * {propertyName}{traitName}
     *
     * @return array
     */
    protected function traitProperties(?string $property = null)
    {
        $property = $property ?? debug_backtrace()[1]['function'];

        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $properties = array_map(function (string $trait) use ($property) {
            return $property . $trait;
        }, $uniqueTraits);

        return array_filter($properties, function (string $property) {
            return property_exists(get_called_class(), $property);
        });
    }
}
