<?php

namespace Modules\SystemNotification\Blueprint\Notification\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class NotificationFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'name' => 'data->message',
                'label' => 'Message',
                'type' => 'text',
            ],
        ];
    }
}
