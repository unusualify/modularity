<?php

namespace Unusualify\Modularous\View\Widgets;

use Illuminate\Support\Arr;
use Unusualify\Modularous\View\ModularousWidget;

class BoardInformationWidget extends ModularousWidget
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
            'class' => 'd-flex flex-column flex-grow-1 min-height-0',
        ],
        'cardAttribute' => [
            'variant' => 'outlined',
            'borderRadius' => '14px',
            'border' => 'sm',
            'borderColor' => 'rgb(var(--v-theme-primary))',
            'titleClass' => 'text-label-large',
            'titleColor' => 'grey',
            'infoClass' => 'text-headline-large pa-0',
            'infoColor' => 'text-primary',
            'class' => 'px-4 py-6 d-flex flex-column flex-grow-1 min-height-0',
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
