<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Arr;
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
        $initialResource = $this->getJSONData();
        $filters = json_decode($this->request->get('filter'), true) ?? [];
        $headers = $this->filterHeadersByRoles($this->getIndexTableColumns());
        $headers = $this->translateHeaders($headers);

        // $i = $this->repository->getModel()->hasUnreadChatMessagesForYou()->latest()->first();
        // dd(
        //     $this->repository->getModel()->hasUnreadChatMessagesForYou()->latest()->get()->map(fn ($i) => [
        //         'press_release_id' => $i->id,
        //         'chat_id' => $i->chat->id,

        //         // 'chat_messages_count' => $i->chatMessages()->count(),
        //         // 'chat_messages_count' => $i->numberOfChatMessages(),
        //         // 'unread_chat_messages_count' => $i->numberOfUnreadChatMessages(),
        //         'unread_chat_messages_for_you_count' => $i->numberOfUnreadChatMessagesForYou(),

        //         // 'unread_messages_count' => $i->unreadChatMessages()->count(),

        //     ])
        //     ->toArray()
        // );
        $tableAttributes = $this->hydrateTableAttributes();
        $tableEndpoints = $this->getIndexUrls() + $this->getUrls();

        $_deprecated = [
            'initialResource' => $initialResource, //
            'tableMainFilters' => $this->getTableMainFilters(),
            'filters' => $filters,
            'requestFilter' => json_decode(request()->get('filter'), true) ?? [],
            'searchText' => request()->has('search') ? request()->query('search') : '', // for current text of search parameter
            'headers' => $headers, // headers to be used in modularity datatable component
            'formSchema' => $this->filterSchemaByRoles($this->formSchema), // input fields to be used in modularity datatable component
        ];
        $data = [
            ...$_deprecated,
            'endpoints' => $tableEndpoints,
        ] + $this->getViewLayoutVariables();

        $options = [
            'moduleName' => $this->getHeadline($this->moduleName),
            'translate' => $this->routeHas('translations') || $this->hasTranslatedInput(),
            'listOptions' => $this->getVuetifyDatatableOptions(), // options to be used in modularity table components in datatable store
            'tableAttributes' => array_merge(
                [
                    'rowActions' => $this->getTableRowActions(),
                    'bulkActions' => $this->getTableBulkActions(),
                    'nestedData' => $this->getNestedData(),
                    'formActions' => $this->getFormActions(),
                    'endpoints' => $tableEndpoints,
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
            'tableStore' => [
                'baseUrl' => rtrim(config('app.url'), '/') . '/',
                'headers' => $headers,
                'searchText' => request()->has('search') ? request()->query('search') : '',
                'options' => $this->getVuetifyDatatableOptions(),
                'data' => $initialResource['data'],
                'total' => $initialResource['total'] ?? 0,
                'mainFilters' => $this->getTableMainFilters(),
                'filter' => ['status' => $filters['status'] ?? $defaultFilterSlug ?? 'all'],
                'advancedFilters' => $this->getTableAdvancedFilters(),
                'customModal' => request()->has('customModal') ? request()->query('customModal') : '',

                // {{-- inputs: {!! json_encode($inputs) !!}, --}}
                // {{-- initialAsync: '{{ count($tableData['data']) ? true : false }}', --}}
                // {{-- name: '{{ $routeName}}', --}}
                // {{-- columns: {!! json_encode($tableColumns) !!}, --}}
            ],
            '__old' => [
                // 'hiddenFilters' => $this->filters(),
                // 'filterLinks' => $this->filterLinks ?? [],

                // 'routeName' => $this->getHeadline($this->routeName),
                // 'translateTitle' => $this->titleIsTranslatable(),
                // 'skipCreateModal' => $this->getIndexOption('skipCreateModal'),
                // 'reorder' => $this->getIndexOption('reorder'),
                // 'permalink' => $this->getIndexOption('permalink'),
                // 'bulkEdit' => $this->getIndexOption('bulkEdit'),
                // 'titleFormKey' => $this->titleFormKey ?? $this->titleColumnKey,
                // 'baseUrl' => $baseUrl,
                // 'permalinkPrefix' => $this->getPermalinkPrefix($baseUrl),
                // 'additionalTableActions' => $this->additionalTableActions(),
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
        $schema = $this->formSchema;

        $item = $this->getFormItem($id);

        $fullRoutePrefix = 'admin.' . ($this->routePrefix ? $this->routePrefix . '.' : '') . $this->moduleName . '.';
        $previewRouteName = $fullRoutePrefix . 'preview';
        $restoreRouteName = $fullRoutePrefix . 'restoreRevision';
        // $baseUrl = $item->urlWithoutSlug ?? $this->getPermalinkBaseUrl();
        // $localizedPermalinkBase = $this->getLocalizedPermalinkBase();

        $itemId = $this->getItemIdentifier($item);

        $data = [
            'translate' => $this->routeHas('translations') || $this->hasTranslatedInput(),
            'formAttributes' => array_merge([
                'modelValue' => $this->repository->getFormFields($item, $this->chunkInputs(all: true, schema: $schema)),
                'title' => __(((bool) $itemId
                    ? 'fields.edit-item'
                    : 'fields.new-item'), ['item' => trans_choice('modules.' . snakeCase($this->routeName), 1)]
                ),
                'isEditing' => $itemId ? true : false,
            ], $this->formAttributes),
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
             ] : []);

        return array_replace_recursive($data, $this->formData($this->request, $item));

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
        return [
            'pageTitle' => $this->getHeadline($this->routeName) . ' Module',
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
