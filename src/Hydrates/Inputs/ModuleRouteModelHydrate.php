<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Modules\Cms\Entities\Concerns\HasPageLayout;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Select options built from all module routes that define an Eloquent model.
 *
 * Config type: {@code module-route-model} → hydrated {@code select} with
 * {@code itemValue} {@code value} (model FQCN) and {@code itemTitle} {@code title} ({@code moduleName - routeName}).
 * Set {@code onlyParentSegmentModels} => true to list only models using {@see HasParentSegment}.
 * Set {@code onlyPageLayoutModels} => true to list only models using {@see HasPageLayout}.
 */
class ModuleRouteModelHydrate extends SelectHydrate
{
    public $selectable = true;

    /**
     * @var array<string, mixed>
     */
    public $requirements = [
        'itemValue' => 'value',
        'itemTitle' => 'title',
        'default' => null,
        'cascadeKey' => 'items',
        'returnObject' => false,
    ];

    public function hydrate()
    {
        $this->input['type'] = 'select';
        $this->input['itemValue'] = 'value';
        $this->input['itemTitle'] = 'title';

        $onlyParentSegmentModels = (bool) ($this->input['onlyParentSegmentModels'] ?? false);
        $onlyPageLayoutModels = (bool) ($this->input['onlyPageLayoutModels'] ?? false);
        $this->input['items'] = Modularous::getModuleRouteModelSelectItems($onlyParentSegmentModels, $onlyPageLayoutModels);

        return parent::hydrate();
    }
}
