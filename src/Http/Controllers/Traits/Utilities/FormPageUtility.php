<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Utilities;

use Illuminate\Database\Eloquent\Model;

trait FormPageUtility
{
    /**
     * @param int|null $id
     * @return Model
     */
    public function getRepositoryItem(&$id = null, $withoutDefaultScopes = false)
    {
        if ($this->isSingleton) {
            $item = $this->repository->getModel()->single();
            $id = $item->id;
        } elseif ($id) {
            // Generate scopes for authorization
            $scopes = $withoutDefaultScopes ? [] : $this->filterScope($this->nestedParentScopes());

            $item = $this->repository->getById(
                $id,
                $this->formWith,
                $this->formWithCount,
                lazy: [],
                scopes: $scopes,
                useDefaultScopes: true
            );

        } else {
            $item = $this->repository->newInstance();
        }

        return $item;
    }

    public function formItem(Model $item): Model
    {
        return $item;
    }

    public function getFormItem($id = null, $withoutDefaultScopes = false, $item = null)
    {
        $this->addFormAppends();

        return $this->getCacheableFormItem($id, function () use ($id, $withoutDefaultScopes, $item) {
            $repositoryItem = ($item instanceof Model ? $item : $this->getRepositoryItem($id, withoutDefaultScopes: $withoutDefaultScopes));
            $item = $this->formItem($repositoryItem);

            $data = [];

            foreach ($this->getFormAppends() as $append) {
                $itemTitle = $append;
                $itemValue = $append;
                preg_match('/(.*) as (.*)/', $append, $matches);
                if ($matches) {
                    $itemTitle = $matches[2];
                    $itemValue = $matches[1];
                }

                $data[$itemTitle] = data_get($item, $itemValue);
            }

            return object_to_array(array_to_object(array_merge([
                'id' => $id,
                $this->titleColumnKey => data_get($item, $this->titleColumnKey, ''),
                'created_at' => $repositoryItem->created_at,
                'updated_at' => $repositoryItem->updated_at,
                'deleted_at' => $repositoryItem->deleted_at,
            ],
                // $item->toArray(),
                $data,
                $this->repository->getFormFields($repositoryItem, $this->formSchema, noSerialization: true),
            )));
        });
    }

    /**
     * @param int|null $itemId
     * @return string
     */
    public function getFormUrl($itemId = null)
    {
        $routeName = $this->moduleRoute?->name()
            ?? $this->moduleRouteName
            ?? $this->routeName;

        return $itemId
            ? $this->getModuleRouteUrl($itemId, 'update', $this->isSingleton)
            : moduleRoute($routeName, $this->routePrefix, 'store', [$this->nestedParentId]);
    }

    /**
     * @param int $id
     * @return array
     */
    protected function getModalFormData($id)
    {
        $item = $this->repository->getById($id, $this->formWith, $this->formWithCount);
        $fields = $this->repository->getFormFields($item);
        $data = [];

        if ($this->routeHasTrait('translations') && isset($fields['translations'])) {
            foreach ($fields['translations'] as $fieldName => $fieldValue) {
                $data['fields'][] = [
                    'name' => $fieldName,
                    'value' => $fieldValue,
                ];
            }

            $data['languages'] = $item->getActiveLanguages();

            unset($fields['translations']);
        }

        foreach ($fields as $fieldName => $fieldValue) {
            $data['fields'][] = [
                'name' => $fieldName,
                'value' => $fieldValue,
            ];
        }

        return array_replace_recursive($data, $this->modalFormData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function modalFormData($request)
    {
        return [];
    }
}
