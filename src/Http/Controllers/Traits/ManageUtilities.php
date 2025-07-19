<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Unusualify\Modularity\Services\View\UWrapper;

trait ManageUtilities
{
    use ManageForm,
        ManageTable,
        Utilities\UrlUtility,
        Utilities\FormPageUtility;

    /**
     * @param array $prependScope
     * @return array
     */
    protected function getIndexData($prependScope = [])
    {
        $headers = $this->filterHeadersByRoles($this->getIndexTableColumns());
        $headers = hydrate_table_columns_translations($headers);

        $scopes = $this->filterScope($this->nestedParentScopes());
        $tableAttributes = $this->hydrateTableAttributes();
        $tableEndpoints = $this->getIndexUrls() + $this->getUrls();
        $tableMainFilters = $this->getTableMainFilters($scopes);

        $data = [
            'endpoints' => $tableEndpoints,
        ] + $this->getViewLayoutVariables();

        $options = [
            'moduleName' => $this->getHeadline($this->moduleName),
            'translate' => $this->routeHas('translations') || $this->hasTranslatedInput(),
            'tableAttributes' => array_merge(
                [
                    'rowActions' => $this->getTableRowActions(),
                    'bulkActions' => $this->getTableBulkActions(),
                    'nestedData' => $this->getNestedData(),
                    'formActions' => $this->getFormActions(),
                    'actions' => $this->getTableActions(),
                    'endpoints' => $tableEndpoints,

                    'navActive' => $this->getConfigFieldsByRoute('default_filter_status', 'all'),
                    'total' => -1,
                    'searchInitialValue' => request()->has('search') ? request()->query('search') : '',
                    'tableOptions' => $this->getVuetifyDatatableOptions(),
                    'columns' => $headers,
                    'filterList' => $tableMainFilters,
                    'filterListAdvanced' => $this->getTableAdvancedFilters(),
                    'openCustomModal' => request()->has('customModal') ? request()->query('customModal') : false,

                    'isModuleTable' => true,
                    'defaultTableOptions' => $this->getDefaultTableOptions(),
                ],
                ($this->isNested ? ['titlePrefix' => $this->nestedParentModel->getTitleValue() . ' \ '] : []),
                array_merge_recursive_preserve(
                    [
                        'name' => $this->getHeadline($this->routeName),
                        'titleKey' => $this->titleColumnKey,
                    ],
                    $tableAttributes,
                ),
                $this->getTableDraggableOptions(),

            ),
            'formStore' => [
                'inputs' => $this->filterSchemaByRoles($this->formSchema),
                'fields' => [],
            ],
            'tableStore' => [],
            '__old' => [
                // 'skipCreateModal' => $this->getIndexOption('skipCreateModal'),
                // 'reorder' => $this->getIndexOption('reorder'),
                // 'permalink' => $this->getIndexOption('permalink'),
                // 'bulkEdit' => $this->getIndexOption('bulkEdit'),
            ],

        ];

        return array_replace_recursive($data + $options, $this->indexData($this->request));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function indexData($request)
    {
        return [];
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $item
     * @return array
     */
    public function indexItemData($item)
    {
        return [];
    }

    /**
     * @param int $id
     * @param \Unusualify\Modularity\Models\Model|null $item
     * @return array
     */
    protected function getFormData($id = null)
    {
        $item = $this->getFormItem($id);
        $customFormData = $this->formData($this->request, $item);
        $schema = $this->formSchema;

        $fullRoutePrefix = 'admin.' . ($this->routePrefix ? $this->routePrefix . '.' : '') . $this->moduleName . '.';
        $previewRouteName = $fullRoutePrefix . 'preview';
        $restoreRouteName = $fullRoutePrefix . 'restoreRevision';
        // $baseUrl = $item->urlWithoutSlug ?? $this->getPermalinkBaseUrl();
        // $localizedPermalinkBase = $this->getLocalizedPermalinkBase();

        $itemId = $this->getItemIdentifier($item);
        $formAttributes = $this->formAttributes;

        $data = [
            'model' => $item,
            'translate' => $this->routeHas('translations') || $this->hasTranslatedInput(),
            'formAttributes' => array_merge([
                'modelValue' => array_merge(
                    $item->toArray(),
                    // $this->repository->getFormFields($item, $this->chunkInputs(all: true, schema: $schema)),
                    $this->repository->getFormFields($item, $schema),
                ),
                'title' => __(((bool) $itemId
                    ? 'fields.edit-item'
                    : 'fields.new-item'), ['item' => trans_choice('modules.' . snakeCase($this->routeName), 1)]
                ),
                'isEditing' => $itemId ? true : false,
                'actions' => $this->getFormActions(),
                ...(($formAttributes['async'] ?? true) ? [] : ['actionUrl' => $this->getFormUrl($itemId)]),
            ], $formAttributes),
            'endpoints' => [
                ((bool) $itemId ? 'update' : 'store') => $this->getFormUrl($itemId),
            ] + $this->getUrls(),
            'formStore' => [
                'inputs' => $this->filterSchemaByRoles($schema),
            ],
            '__old' => [
                // 'editable' => !!$itemId,
                // 'moduleName' => $this->moduleName,
                // 'routeName' => $this->routeName,
                // 'routePrefix' => $this->routePrefix,
                // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,
                // 'publish' => $item->canPublish ?? true,
                // 'publishDate24Hr' => Config::get('twill.publish_date_24h') ?? false,
                // 'publishDateFormat' => Config::get('twill.publish_date_format') ?? null,
                // 'publishDateDisplayFormat' => Config::get('twill.publish_date_display_format') ?? null,
                // 'translate' => $this->routeHasTrait('translations'),
                // 'translateTitle' => $this->titleIsTranslatable(),
                // 'permalink' => $this->getIndexOption('permalink'),
                // 'createWithoutModal' => ! $itemId && $this->getIndexOption('skipCreateModal'),
                // 'form_fields' => $this->repository->getFormFields($item),
                // 'baseUrl' => $baseUrl ?? '',
                // 'localizedPermalinkBase'=>$localizedPermalinkBase ?? '',
                // 'permalinkPrefix' => $this->getPermalinkPrefix($baseUrl ?? ''),
                // 'editor' => Config::get('twill.enabled.block-editor') && $this->routeHasTrait('blocks') && ! $this->disableEditor,
                // 'blockPreviewUrl' => Route::has('admin.blocks.preview') ? URL::route('admin.blocks.preview') : '#',
                // 'availableRepeaters' => $this->getRepeaterList()->toJson(),
                // 'revisions' => $this->routeHasTrait('revisions') ? $item->revisionsArray() : null,
            ],

        ] + (Route::has($previewRouteName) && $itemId ? [
            'previewUrl' => moduleRoute($this->moduleName, $this->routePrefix, 'preview', [$itemId]),
        ] : [])
             + (Route::has($restoreRouteName) && $itemId ? [
                 'restoreUrl' => moduleRoute($this->moduleName, $this->routePrefix, 'restoreRevision', [$itemId]),
             ] : [])
             + $this->getViewLayoutVariables();

        return array_replace_recursive($data, $customFormData);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function formData($request, $item = null)
    {
        return [];
    }

    public function getViewLayoutVariables()
    {
        $currentRoute = Route::current();
        $currentActionMethod = $currentRoute->getActionMethod();
        $snakeRouteName = Str::snake($this->routeName);
        $translationRouteKey = "modules.{$snakeRouteName}";

        // Check for custom title from configuration first
        $customTitle = $this->tableAttributes['customTitle'] ?? null;

        switch ($currentActionMethod) {
            case 'create':
                $pageTitle = trans_choice($translationRouteKey, 1);
                $headerTitle = $customTitle ?: __('fields.new-item', ['item' => trans_choice('modules.' . snakeCase($this->routeName), 1)]);

                break;
            case 'edit':
                $pageTitle = trans_choice($translationRouteKey, 1);
                $headerTitle = $customTitle ?: __('fields.edit-item', ['item' => trans_choice('modules.' . snakeCase($this->routeName), 1)]);

                break;
            case 'show':
                $pageTitle = trans_choice($translationRouteKey, 1);
                $headerTitle = $customTitle ?: __('fields.show-item', ['item' => trans_choice('modules.' . snakeCase($this->routeName), 1)]);

                break;
            default:
                $pageTitle = $customTitle ?: trans_choice($translationRouteKey, 0);
                $headerTitle = $customTitle ?: trans_choice($translationRouteKey, 0);
        }

        return [
            'pageTitle' => "$pageTitle - " . \Unusualify\Modularity\Facades\Modularity::pageTitle(),
            'headerTitle' => $headerTitle,
        ];
    }

    public function getNestedData()
    {

        $result = Collection::make($this->getConfigFieldsByRoute('modules', []))->map(function ($context, $key) {

            $context->title = isset($context->title) ? ___($context->title) : headline($context->title);

            if (isset($context->type)) {
                if ($context->type == 'formWrapper') {
                    $forms = Collection::make($context->elements)->map(function ($element) {

                        $routeName = Route::hasAdmin($element->route);

                        if (! $routeName) {
                            return false;
                        }

                        $schema = $this->createFormSchema(getFormDraft($element->draft));

                        $parameters = Collection::make(Route::getRoutes()->getByName($routeName)->parameterNames())->mapWithKeys(function ($parameter, $j) {
                            return [$parameter => ":{$parameter}"];
                        })->toArray();

                        $modelValueAbstract = isset($element->relation) ? $element->relation : $this->getSnakeCase($this->routeName);

                        return [
                            'title' => isset($element->title) ? ___($element->title) : '',
                            'buttonText' => 'update',
                            'hasSubmit' => true,
                            'modelValue' => "\${$modelValueAbstract}",
                            // 'modelValue' => [],
                            'schema' => $schema,
                            'defaultItem' => collect($schema)->mapWithKeys(function ($item, $key) {
                                return [$item['name'] => $item['default'] ?? ''];
                                $carry[$key] = $item->default ?? '';
                            })->toArray(),
                            'actionUrl' => route($routeName, $parameters),
                        ];
                    })->toArray();

                    $context->elements = UWrapper::makeFormWrapper($forms);
                }
                unset($context->type);
            }

            return $context;
        })->toArray();

        return $result;
    }

    protected function addIndexWithsNestedData(): array
    {
        $withs = [];

        foreach ($this->getConfigFieldsByRoute('modules', []) as $key => $item) {
            if (isset($item->type) && $item->type == 'formWrapper') {
                foreach ($item->elements as $element) {
                    if (isset($element->relation)) {
                        $withs[] = $element->relation;
                    }
                }
            }
        }

        return $withs;
    }
}
