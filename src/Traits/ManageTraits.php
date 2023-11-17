<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Collection;

trait ManageTraits {

    /**
     * @param string|null $method
     * @return array
     */
    protected function traitsMethods(string $method = null)
    {
        $method = $method ?? debug_backtrace()[1]['function'];

        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $methods = array_map(function (string $trait) use ($method) {
            return $method . $trait;
        }, $uniqueTraits);

        return array_filter($methods, function (string $method) {
            return method_exists(get_called_class(), $method);
        });
    }
}
