<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Unusualify\Modularity\Traits\Allowable;
trait TableAttributes
{
    use Allowable;
    /**
     * @var array
     */
    protected $defaultTableAttributes = [
        // 'embeddedForm' => false,
        // 'createOnModal' => true,
        // 'editOnModal' => true,
        // 'formWidth' => '60%',
        // 'isRowEditing' => false,
        // 'rowActionsType' => 'inline',
        // 'hideDefaultFooter' => false,
        // 'striped' => true,
        // 'hideBorderRow' => true,
        // 'roundedRows' => true,
    ];

    /**
     * getTableOptions
     *
     * @return void
     */
    public function getTableAttributes()
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve(
                        $this->defaultTableAttributes,
                        object_to_array($this->getConfigFieldsByRoute('table_attributes') ?? $this->getConfigFieldsByRoute('table_options') ?? (object) []))
                )->toArray();
            } catch (\Throwable $th) {
                return $this->defaultTableAttributes;
            }
        }

        return $this->defaultTableAttributes;
    }

    /**
     * method that checks whether the attribute configured on table_options
     * and returns its value or false if not.
     *
     *
     * @param mixed $attribute
     * @return bool|mixed returns referenced value or false if it's not defined at module config->table_options
     */
    public function getTableAttribute($attribute)
    {
        return $this->tableAttributes[$attribute] ?? null;
    }

    /**
     * Hydrate the table attributes
     * @return void
     */
    protected function hydrateTableAttributes()
    {
        $attributes = $this->tableAttributes;

        if (isset($attributes['customRow'])) {
            // Handle associative array case
            if (Arr::isAssoc($attributes['customRow'])) {
                if (isset($attributes['customRow']['name'])) {
                    $attributes['customRow'] = $this->hydrateCustomRow($attributes['customRow']);
                }
            }
            // Handle sequential array case with role-based filtering
            else {
                $firstMatch = [];
                foreach ($attributes['customRow'] as $component) {
                    // Skip if component doesn't pass role check
                    if(!$this->isAllowedItem($component, searchKey: 'allowedRoles')) {
                        continue;
                    }

                    // Convert first matching component to standard format and break
                    if (isset($component['name'])) {
                        $firstMatch = $this->hydrateCustomRow($component);

                        break;
                    }
                }

                $attributes['customRow'] = $firstMatch;
            }
        }

        return $attributes;
    }

    /**
     * Set the table attributes
     * @param array $tableOptions
     * @return void
     */
    protected function setTableAttributes($tableOptions = null)
    {
        if ($tableOptions) {
            $this->tableAttributes = array_merge_recursive_preserve(
                $this->defaultTableAttributes,
                $tableOptions,
            );
        }

        return $this;
    }

    /**
     * Hydrate the custom row
     * @param array $customRow
     * @return array
     */
    protected function hydrateCustomRow($customRow)
    {
        return array_merge_recursive_preserve(
            ['col' => ['cols' => 12]],
            array_diff_key($customRow, ['allowedRoles' => ''])
        );
    }

    /**
     * Add relations on index page
     * @return array
     */
    protected function addIndexWithsTableHeaders(): array
    {
        $withs = [];

        $rawHeaders = $this->getConfigFieldsByRoute('headers', []);

        if(count($rawHeaders) > 0){
            $model = $this->repository->getModel();
            if(method_exists($model, 'hasRelation')) {
                foreach ($rawHeaders as $header) {
                    if(isset($header->with)) {
                        $with = is_string($header->with) ? [$header->with] : (array) $header->with;

                        if(Arr::isAssoc($with)) {
                            foreach($with as $relationshipName => $mappings) {
                                if(isset($mappings['functions'])) {
                                    $withs[$relationshipName] = fn($query) => array_reduce($mappings['functions'], fn($query, $function) => $query->$function(), $query);
                                } else {
                                    $withs[$relationshipName] = $mappings;
                                }
                            }
                        } else {
                            $withs[] = $with;
                        }
                    }
                }
            }
        }

        return $withs;
    }
}
