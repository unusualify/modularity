<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\View;

use Unusualify\Modularous\Services\View\UWidget;
use Unusualify\Modularous\Tests\TestCase;

class UWidgetTest extends TestCase
{
    public function test_set_attributes_prefers_col_payload_for_v_col_widgets(): void
    {
        $widget = new UWidget;
        $widget->tag = 'v-col';
        $widget->setAttributes([
            'col' => ['cols' => 6, 'class' => 'pa-2'],
        ]);

        $this->assertSame(['cols' => 6, 'class' => 'pa-2'], $widget->attributes);
    }

    public function test_set_attributes_keeps_full_payload_without_col_key(): void
    {
        $widget = UWidget::make()
            ->makeComponent('v-col')
            ->setAttributes([
                'cols' => 12,
                'class' => 'pa-4',
            ]);

        $this->assertSame(['cols' => 12, 'class' => 'pa-4'], $widget->attributes);
    }

    public function test_set_widget_attributes_skips_connector_components_without_connector(): void
    {
        $widget = UWidget::make()
            ->makeComponent('v-col')
            ->setAttributes([
                'component' => 'ue-table',
                'attributes' => ['dense' => true],
            ]);

        $this->assertSame('v-col', $widget->render()['tag']);
        $this->assertArrayNotHasKey('elements', $widget->render());
    }
}
