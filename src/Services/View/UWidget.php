<?php

namespace Unusualify\Modularity\Services\View;

use Illuminate\Support\Str;

class UWidget extends UComponent
{
    public function setAttributes($attributes = [])
    {
        if (isset($attributes['col'])) {
            $this->attributes = $attributes['col'];
        } else {
            $this->attributes = $attributes;
        }
        $this->setWidgetAttributes($attributes);

        return $this;
    }

    public function setWidgetAttributes($attributes)
    {
        $methodName = null;
        // dd($attributes);
        if (isset($attributes['component']) && $this->tag == 'v-col') {
            $methodName = 'set' . Str::studly(str_replace('ue-', '', $attributes['component'])) . 'Attributes';
            // dd($methodName);
            // dd(method_exists($this, $methodName), $methodName, Str::studly(str_replace('ue-','',$attributes['component'])));
            if (method_exists($this, $methodName)) {
                $this->addChildren($this->$methodName($attributes));

                return;
            } else {
                // dd($attributes);
                $this->addChildren(($this->setComponentAttributes($attributes)));
            }
        }
    }

    protected function setTableAttributes($attributes)
    {
        if (! isset($attributes['connector'])) {
            return null;
        }

        $data = init_connector($attributes['connector']);
        $data['items'] = $data['items']->toArray();

        $table = new UWidget;

        return $table->makeComponent(
            $attributes['component'],
            array_merge(
                $attributes['attributes'],
                [
                    'items' => $data['items'],
                    'route' => $data['route'],
                    'repository' => $data['repository'],
                    'module' => $data['module'],
                ]
            )
        );
    }

    // Experimental left for general components maybe

    protected function setComponentAttributes($attributes)
    {
        // dd($attributes['connector']);
        if (! isset($attributes['connector'])) {
            return null;
        }

        $data = init_connector($attributes['connector']);
        $data['items'] = $data['items']->toArray();

        $table = new UWidget;

        // dd($attributes['component']);
        return $table->makeComponent(
            $attributes['component'],
            array_merge(
                $attributes['attributes'],
                [
                    'items' => $data['items'],
                    'route' => $data['route'],
                    'repository' => $data['repository'],
                    'module' => $data['module'],
                ]
            )
        );
    }

    protected function setBoardInformationPlusAttributes($attributes)
    {
        $boardInformation = new UWidget;
        foreach ($attributes['cards'] as $card) {
            if (isset($card['connector'])) {
                $data = init_connector($card['connector']);
                $card['data'] = $data;
                $attributes['attributes']['cards'][] = $card;
                // dd($data);
            }
        }
        // dd($attributes);
        $boardInformation->makeComponent($attributes['component'], $attributes['attributes']);

        // dd($test);
        return $boardInformation->makeComponent($attributes['component'], $attributes['attributes']);
    }

    // UWidget::makeTable()->setAttributes();

}
