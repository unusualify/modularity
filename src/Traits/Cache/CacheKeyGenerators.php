<?php

namespace Unusualify\Modularous\Traits\Cache;

use Illuminate\Support\Arr;
use Unusualify\Modularous\Facades\ModularousCache;

/**
 * Cache key generator methods.
 *
 * @requires method getPrefix() - Get cache prefix
 */
trait CacheKeyGenerators
{
    /**
     * Generate a cache key for a specific record.
     */
    public function generateRecordKey(string $moduleName, string $moduleRouteName, $id): string
    {
        return ModularousCache::generateCacheKey($moduleName, $moduleRouteName, 'record', ['id' => $id]);
    }

    /**
     * Generate a cache key for a specific type.
     */
    protected function createCacheKey($moduleName, $moduleRouteName, string $specifierKey, array $specifierData): string
    {
        return ModularousCache::generateCacheKey($moduleName, $moduleRouteName, $specifierKey, $specifierData);
    }

    /**
     * Resolve the cache specifiers for a specific type.
     */
    protected function resolveCacheSpecifiers(string $type, array $specifierData): array
    {
        $specifierKey = null;

        switch ($type) {
            case 'counts':
            case 'count':
                if (! isset($specifierData['slug'])) {
                    throw new \InvalidArgumentException('Slug is required for count cache type');
                }
                $type = 'counts';
                $specifierKey = 'count:' . $specifierData['slug'];
                $specifierData = Arr::except($specifierData, ['slug']);

                break;
            case 'index':
                $specifierKey = 'index';

                break;
            case 'formattedItem':
                if (! isset($specifierData['id'])) {
                    throw new \InvalidArgumentException('ID is required for formatted item cache type');
                }
                $specifierKey = 'formattedItem:' . $specifierData['id'];
                $specifierData = Arr::except($specifierData, ['id']);

                break;
            case 'formItem':
                if (! isset($specifierData['id'])) {
                    throw new \InvalidArgumentException('ID is required for form item cache type');
                }
                $specifierKey = 'formItem:' . $specifierData['id'];
                $specifierData = Arr::except($specifierData, ['id']);

                break;
            case 'presentationItem':
                if (! isset($specifierData['id'])) {
                    throw new \InvalidArgumentException('ID is required for presentation item cache type');
                }
                $specifierKey = 'presentationItem:' . $specifierData['id'];
                $specifierData = Arr::except($specifierData, ['id']);

                break;
            case 'record':
                throw new \InvalidArgumentException('Record cache type is not ready yet');
                if (! isset($specifierData['id'])) {
                    throw new \InvalidArgumentException('ID is required for record cache type');
                }
                $specifierKey = 'record:' . $specifierData['id'];
                $specifierData = Arr::except($specifierData, ['id']);

                break;
            default:
                throw new \InvalidArgumentException("Invalid cache type: $type");

                break;
        }

        $specifierData = method_exists($this, 'addUserContext') ? $this->addUserContext($specifierData) : $specifierData;

        return [$type, $specifierKey, $specifierData];
    }
}
