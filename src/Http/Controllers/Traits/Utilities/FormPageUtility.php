<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

trait FormPageUtility
{
    /**
     * @param int|null $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getFormItem($id = null)
    {
        if ($this->isSingleton) {
            $item = $this->repository->getModel()->single();
        } elseif ($id) {
            $item = $this->repository->getById(
                $id,
                $this->formWith,
                $this->formWithCount
            );
        } else {
            $item = $this->repository->newInstance();
        }

        return $item;
    }

    /**
     * @param int|null $itemId
     * @return string
     */
    public function getFormUrl($itemId = null)
    {
        try {
            $url = $itemId
                ? $this->getModuleRoute($itemId, 'update', $this->isSingleton)
                : moduleRoute($this->routeName, $this->routePrefix, 'store', [$this->nestedParentId]);
            // code...
        } catch (\Throwable $th) {
            dd($th, $this->routeName, $this->routePrefix, $this->nestedParentId, $this->isNested);
        }

        return $url;
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
