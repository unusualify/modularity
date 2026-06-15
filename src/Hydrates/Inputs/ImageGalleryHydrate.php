<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Hydrates\Inputs;

class ImageGalleryHydrate extends InputHydrate
{
    /**
     * @var array<string, mixed>
     */
    public $requirements = [
        'name' => 'images',
        'translated' => false,
        'default' => [],
        'max' => 200,
    ];

    /**
     * @return array<string, mixed>
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-image-gallery';
        $input['label'] ??= __('Image Gallery');
        $input['max'] ??= 200;

        return $input;
    }
}
