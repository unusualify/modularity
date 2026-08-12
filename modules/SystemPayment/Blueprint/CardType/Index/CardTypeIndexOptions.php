<?php

namespace Modules\SystemPayment\Blueprint\CardType\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

final class CardTypeIndexOptions implements ModuleRouteTableOptionsProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'createOnModal' => true,
            'editOnModal' => true,
            'isRowEditing' => false,
            'rowActionsType' => 'inline',
        ];
    }
}
