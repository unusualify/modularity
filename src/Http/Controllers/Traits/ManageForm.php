<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Facades\Config;

trait ManageForm
{
    use Form\FormAttributes,
        Form\FormSchema,
        Form\FormActions;

    /**
     * @param \Illuminate\Foundation\Application $app
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function __afterConstructManageForm($app, $request)
    {
        $this->defaultFormAttributes = (array) Config::get(modularityBaseKey() . '.default_form_attributes');

        $this->formAttributes = array_merge_recursive_preserve($this->getFormAttributes(), $this->formAttributes ?? []);
    }

    /**
     * @return array
     */
    protected function addWithsManageForm(): array
    {
        return collect(array_to_object($this->formSchema))->filter(function ($input) {
            // return $this->hasWithModel($item['type']);
            return in_array($input->type, [
                'treeview',
                'input-treeview',
                // 'checklist',
                // 'input-checklist',
                'select',
                'combobox',
                'autocomplete',
                'input-repeater',
            ]) && ! (isset($input->ext) && $input->ext == 'morphTo');
        })->mapWithKeys(function ($input) {

            if ($input->type == 'input-repeater') {
                if (isset($input->ext) && $input->ext == 'relationship') {
                    return [$input->name];

                    // try {
                    //     $relationships =  method_exists($this->repository->getModel(), 'getDefinedRelations')
                    //         ? $this->repository->getDefinedRelations()
                    //         : $this->repository->modelRelations();

                    //     return in_array($relationshipName, $relationships)
                    //         ? [$relationshipName]
                    //         : [];
                    // } catch (\Throwable $th) {
                    //     dd(
                    //         $th,
                    //         $this->repository,
                    //         $relationshipName
                    //     );
                    // }

                } else {
                    return [];
                }
            } else {
                $relationship = $this->getCamelNameFromForeignKey($input->name) ?: $input->name;
            }

            if (in_array($input->type, ['select', 'combobox', 'autocomplete']) && ! isset($input->repository)) {
                return [];
            }

            $relationshipsTypes = [];

            if (method_exists($this->repository->getModel(), 'definedRelationsTypes')) {
                $relationshipsTypes = $this->repository->definedRelationsTypes();
            }

            $relationType = null;

            if (array_key_exists($relationship, $relationshipsTypes)) {
                $relationType = $relationshipsTypes[$relationship];
            }

            if (in_array($relationType, ['MorphToMany', 'BelongsToMany'])) {
                return [
                    $relationship,
                ];
            }

            return [
                $relationship => [
                    // ['select', $item['itemValue'], $item['itemTitle']],
                    ['addSelect', $input->itemValue ?? 'id'],
                    ['addSelect', $input->itemTitle ?? 'name'],
                ],
            ];
        })->toArray();
    }
}
