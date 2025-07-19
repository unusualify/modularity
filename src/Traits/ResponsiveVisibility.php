<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Collection;

trait ResponsiveVisibility
{
    /**
     * The available breakpoints for responsive visibility
     *
     * @var array
     */
    protected $responsiveBreakpoints = ['sm', 'md', 'lg', 'xl', 'xxl'];

    /**
     * The key to search for responsive settings in items
     *
     * @var string
     */
    protected $responsiveSearchKey = 'responsive';

    /**
     * Get items with responsive classes applied
     *
     * @param array|Collection $items
     * @param string|null $searchKey
     */
    public function getResponsiveItems($items, $searchKey = null): array|Collection
    {
        $isArray = is_array($items);

        if ($isArray) {
            $items = collect($items);
        } elseif (! $items instanceof Collection) {
            throw new \Exception('Invalid items type, must be an array or a collection');
        }

        $searchKey = $searchKey ?? $this->responsiveSearchKey;

        $newItems = $items->map(function ($item) use ($searchKey) {
            return $this->applyResponsiveClasses($item, $searchKey);
        });

        if ($isArray) {
            return $newItems->toArray();
        }

        return $newItems;
    }

    /**
     * Apply responsive classes to a single item
     *
     * @param array|object $item
     * @param string|null $searchKey
     * @return array|object
     */
    public function applyResponsiveClasses($item, $searchKey = null, $display = 'flex', $classNotation = 'class')
    {
        if (! in_array($display, ['flex', 'block', 'inline-block', 'inline'])) {
            throw new \Exception('Invalid display value, must be one of: flex, block, inline-block, inline');
        }

        $searchKey = $searchKey ?? $this->responsiveSearchKey;
        $itemType = is_array($item) ? 'array' : 'object';

        $hasResponsiveSettings = $itemType == 'array'
            ? isset($item[$searchKey])
            : isset($item->{$searchKey});

        if (! $hasResponsiveSettings) {
            return $item;
        }

        $responsiveSettings = $itemType == 'array'
            ? $item[$searchKey]
            : $item->{$searchKey};

        $responsiveClasses = $this->generateResponsiveClasses($responsiveSettings, $display);

        if (empty($responsiveClasses)) {
            return $item;
        }

        // Get existing classes
        $existingClasses = $itemType == 'array'
            ? (data_get($item, $classNotation) ?? '')
            : (data_get($item, $classNotation) ?? '');

        // Combine classes
        $newClasses = trim($existingClasses . ' ' . implode(' ', $responsiveClasses));

        // Update item with new classes
        if ($itemType == 'array') {
            data_set($item, $classNotation, $newClasses);
        } else {
            data_set($item, $classNotation, $newClasses);
        }

        return $item;
    }

    /**
     * Generate responsive classes based on settings
     *
     * @param array $settings
     */
    protected function generateResponsiveClasses($settings, $display = 'flex'): array
    {
        $classes = [];

        if (is_object($settings)) {
            $settings = (array) $settings;
        }

        if (! is_array($settings)) {
            return $classes;
        }

        // Handle hideOn configuration
        if (isset($settings['hideOn'])) {
            $hideOn = is_array($settings['hideOn']) ? $settings['hideOn'] : [$settings['hideOn']];
            foreach ($hideOn as $breakpoint) {
                if (in_array($breakpoint, $this->responsiveBreakpoints)) {
                    $classes[] = "d-{$breakpoint}-none";
                }
            }
        }

        // Handle showOn configuration
        if (isset($settings['showOn'])) {
            $showOn = is_array($settings['showOn']) ? $settings['showOn'] : [$settings['showOn']];

            // Hide by default, then show on specified breakpoints
            $classes[] = 'd-none';
            foreach ($showOn as $breakpoint) {
                if (in_array($breakpoint, $this->responsiveBreakpoints)) {
                    $classes[] = "d-{$breakpoint}-{$display}";
                }
            }
        }

        // Handle hideBelow configuration
        if (isset($settings['hideBelow'])) {
            $hideBelow = $settings['hideBelow'];
            $breakpointIndex = array_search($hideBelow, $this->responsiveBreakpoints);

            if ($breakpointIndex !== false) {
                $classes[] = 'd-none';
                $classes[] = "d-{$hideBelow}-{$display}";
            }
        }

        // Handle hideAbove configuration
        if (isset($settings['hideAbove'])) {
            $hideAbove = $settings['hideAbove'];
            $breakpointIndex = array_search($hideAbove, $this->responsiveBreakpoints);

            if ($breakpointIndex !== false) {
                $breakpointsToHide = array_slice($this->responsiveBreakpoints, $breakpointIndex + 1);
                foreach ($breakpointsToHide as $breakpoint) {
                    $classes[] = "d-{$breakpoint}-none";
                }
            }
        }

        // Handle custom breakpoint visibility
        if (isset($settings['breakpoints'])) {
            foreach ($settings['breakpoints'] as $breakpoint => $visible) {
                if (in_array($breakpoint, $this->responsiveBreakpoints)) {
                    if ($visible === false) {
                        $classes[] = "d-{$breakpoint}-none";
                    } elseif ($visible === true) {
                        $classes[] = "d-{$breakpoint}-{$display}";
                    }
                }
            }
        }

        return array_unique($classes);
    }

    /**
     * Check if an item has responsive settings
     *
     * @param array|object $item
     * @param string|null $searchKey
     */
    public function hasResponsiveSettings($item, $searchKey = null): bool
    {
        $searchKey = $searchKey ?? $this->responsiveSearchKey;
        $itemType = is_array($item) ? 'array' : 'object';

        return $itemType == 'array'
            ? isset($item[$searchKey])
            : isset($item->{$searchKey});
    }
}
