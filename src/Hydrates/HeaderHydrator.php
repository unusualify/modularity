<?php

namespace Unusualify\Modularity\Hydrates;

use Unusualify\Modularity\Traits\ResponsiveVisibility;

class HeaderHydrator
{
    use ResponsiveVisibility;

    public function __construct(
        protected $header,
        protected $module,
        protected $routeName,
    ) {}

    public function hydrate(): array
    {
        $header = $this->header;
        // switch column
        if (isset($header['formatter']) && count($header['formatter']) && $header['formatter'][0] == 'switch') {
            $header['width'] = '20px';
            // $header['align'] = 'center';
        }

        if (isset($header['sortable']) && $header['sortable']) {
            if (preg_match('/(.*)(_relation)/', $header['key'], $matches)) {
                $header['sortable'] = false;
            }

        }

        if ($header['key'] == 'actions') {
            $header['width'] ??= 100;
            $header['align'] ??= 'center';
            $header['sortable'] ??= false;
        }

        if(isset($header['noMobile']) && $header['noMobile']) {
            $header['responsive'] ??= [];
            $header['responsive'] = [
                'hideBelow' => 'md',
            ];
        }

        if(isset($header['responsive']) && $header['responsive']) {
            $header = $this->applyResponsiveClasses($header, 'responsive', 'table-cell', 'cellProps.class');
        }

        $header = array_merge_recursive_preserve($this->defaultHeader(), $header);

        $header['visible'] ??= true;

        return $header;
    }

    protected function defaultHeader()
    {
        return modularityConfig('default_header');
    }
}
