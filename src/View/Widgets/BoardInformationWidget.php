<?php

namespace Unusualify\Modularity\View\Widgets;

use Illuminate\Support\Arr;
use Unusualify\Modularity\View\ModularityWidget;

class BoardInformationWidget extends ModularityWidget
{
    public $tag = 'ue-board-information-plus';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 6,
        'xl' => 4,
    ];

    public $attributes = [
        'class' => 'elevation-2',
        'container' => [
            'color' => '',
            'elevation' => 2,
            'class' => 'h-100',
        ],
        'cardAttribute' => [
            'variant' => 'outlined',
            'borderRadius' => '14px',
            'border' => 'sm',
            'borderColor' => 'rgb(var(--v-theme-primary))',
            'titleClass' => 'text-subtitle-2',
            'titleColor' => 'grey',
            'infoClass' => 'text-h4 pa-0',
            'infoColor' => 'text-primary',
            'class' => 'px-4 py-6 h-100',
            'infoLineHeight' => '1',
            'infoFontWeight' => '700',
        ],
    ];

    public function hydrateAttributes($attributes)
    {
        $attributes = parent::hydrateAttributes($attributes);

        if (isset($attributes['cards'])) {
            $cards = [];
            foreach ($attributes['cards'] as $card) {
                if (is_array($card) && Arr::isAssoc($card) && isset($card['connector'])) {
                    $data = init_connector($card['connector']);
                    $card['data'] = $data;
                    $cards[] = $card;
                }
            }
            $attributes['cards'] = $cards;
        }

        return $attributes;
    }
}
