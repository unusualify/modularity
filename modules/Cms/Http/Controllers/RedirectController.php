<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\RedirectValidationServiceInterface;
use Modules\Cms\Entities\Redirect;
use Unusualify\Modularous\Contracts\CanBulkSheet;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Support\ModularousFlashWarnings;

class RedirectController extends BaseController implements CanBulkSheet
{
    private const CSV_ALLOWED_STATUS = [301, 302, 307, 308];

    protected $moduleName = 'Cms';

    protected $routeName = 'Redirect';

    protected $fieldsPermissions = [];

    /**
     * Non-blocking messages from {@see RedirectValidationServiceInterface::validate()} (e.g. cross-locale).
     *
     * @var array<int, string>|null
     */
    protected ?array $redirectValidationWarnings = null;

    public function __construct(Application $app, Request $request)
    {
        if (modularousConfig('security.enabled', false)) {
            $permission = modularousConfig('security.critical_field_permissions.redirect_from', 'redirect_edit');
            $this->fieldsPermissions = [
                'from_path' => $permission,
                'to_path' => modularousConfig('security.critical_field_permissions.redirect_to', $permission),
                'status_code' => modularousConfig('security.critical_field_permissions.status_code', $permission),
            ];
        }

        parent::__construct($app, $request);
    }

    public function store($parentId = null)
    {
        $this->validateRedirectRules();

        return $this->withRedirectValidationWarnings(parent::store($parentId));
    }

    public function update($id, $submoduleId = null)
    {
        $this->validateRedirectRules((int) $id);

        return $this->withRedirectValidationWarnings(parent::update($id, $submoduleId));
    }

    /**
     * @param mixed $response
     * @return mixed
     */
    protected function withRedirectValidationWarnings($response)
    {
        $warnings = $this->redirectValidationWarnings ?? [];
        $this->redirectValidationWarnings = null;

        if ($warnings === []) {
            return $response;
        }

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (is_array($data)) {
                $existing = $data['warnings'] ?? [];
                $data['warnings'] = array_values(array_merge(
                    is_array($existing) ? $existing : [],
                    array_values($warnings)
                ));
                $response->setData($data);
            }

            return $response;
        }

        if ($response instanceof RedirectResponse) {
            ModularousFlashWarnings::merge($warnings);
        }

        return $response;
    }

    protected function validateRedirectRules(?int $id = null): void
    {
        if (! $this->request->has('from_path')) {
            return;
        }

        $fromPath = (string) $this->request->input('from_path', '');
        $toPath = (string) $this->request->input('to_path', '');
        $locale = (string) $this->request->input('locale', app()->getLocale());

        /** @var RedirectValidationServiceInterface $validator */
        $redirectValidationService = app()->make(RedirectValidationServiceInterface::class);

        $existing = $this->repository->query()
            ->when($id !== null, fn ($q) => $q->where('id', '<>', $id))
            ->get(['from_path', 'to_path'])
            ->mapWithKeys(fn ($row) => [$row->from_path => $row->to_path])
            ->toArray();

        $validation = $redirectValidationService->validate($fromPath, $toPath, [
            'existing_redirects' => $existing,
            'locale' => $locale,
        ]);

        $this->redirectValidationWarnings = array_values(array_filter(
            $validation['warnings'] ?? [],
            static fn ($w) => $w !== null && $w !== ''
        ));

        if (! ($validation['valid'] ?? false)) {
            throw ValidationException::withMessages([
                'from_path' => $validation['errors'] ?? ['Invalid redirect rule.'],
            ]);
        }
    }

    /**
     * @return list<array{key: string, label: string, required?: bool, aliases?: list<string>}>
     */
    public function bulkSheetFields(): array
    {
        $fields = $this->bulkSheetRouteConfig()['fields'] ?? null;
        if (is_array($fields) && $fields !== []) {
            /** @var list<array{key: string, label: string, required?: bool, aliases?: list<string>}> $fields */
            return $fields;
        }

        return [
            ['key' => 'locale', 'label' => 'Locale', 'required' => true, 'aliases' => ['locale']],
            ['key' => 'from_path', 'label' => 'From path', 'required' => true, 'aliases' => ['from', 'source']],
            ['key' => 'to_path', 'label' => 'To path', 'required' => true, 'aliases' => ['to', 'target', 'destination']],
            ['key' => 'status_code', 'label' => 'Status code', 'required' => false, 'aliases' => ['code']],
            ['key' => 'is_active', 'label' => 'Active', 'required' => false, 'aliases' => ['active']],
        ];
    }

    public function bulkSheetPrepareAndValidateRows(array $records): array
    {
        $canonical = $this->app->make(CanonicalUrlResolverInterface::class);
        $redirectValidationService = $this->app->make(RedirectValidationServiceInterface::class);
        /** @var Collection<int, Redirect> $dbRedirects */
        $dbRedirects = $this->repository->query()->get();

        $result = [];
        $batchNormKeys = [];
        $priorEdges = [];

        foreach ($records as $record) {
            $line = $record['line'];
            $v = $record['values'];
            $errors = [];
            $warnings = [];

            $locale = $v['locale'] ?? '';
            if ($locale === '') {
                $errors[] = 'locale is required.';
            }

            $fromRaw = $v['from_path'] ?? '';
            $toRaw = $v['to_path'] ?? '';
            if ($fromRaw === '') {
                $errors[] = 'from_path is required.';
            }
            if ($toRaw === '') {
                $errors[] = 'to_path is required.';
            }

            $statusCode = 301;
            if (isset($v['status_code']) && $v['status_code'] !== '') {
                if (! ctype_digit($v['status_code'])) {
                    $errors[] = 'status_code must be an integer.';
                } else {
                    $statusCode = (int) $v['status_code'];
                    if (! in_array($statusCode, self::CSV_ALLOWED_STATUS, true)) {
                        $errors[] = 'status_code must be one of 301, 302, 307, 308.';
                    }
                }
            }

            $isActive = true;
            if (isset($v['is_active']) && $v['is_active'] !== '') {
                $b = mb_strtolower(trim((string) $v['is_active']));
                if (! in_array($b, ['0', '1', 'true', 'false', 'yes', 'no', 'on', 'off'], true)) {
                    $errors[] = 'is_active must be 0, 1, true, false, yes, no, on, or off.';
                } else {
                    $isActive = ! in_array($b, ['0', 'false', 'no', 'off'], true);
                }
            }

            $normFrom = $fromRaw !== '' ? $canonical->normalizePath($fromRaw) : '';
            $normTo = $toRaw !== '' ? $canonical->normalizePath($toRaw) : '';

            $graph = [];
            foreach ($dbRedirects as $redirectRow) {
                /** @var Redirect $redirectRow */
                if ($redirectRow->locale === $locale
                    && $canonical->normalizePath((string) $redirectRow->from_path) === $normFrom) {
                    continue;
                }
                $graph[$canonical->normalizePath((string) $redirectRow->from_path)] = $canonical->normalizePath((string) $redirectRow->to_path);
            }
            foreach ($priorEdges as $pf => $pt) {
                $graph[$pf] = $pt;
            }

            if ($errors === [] && $fromRaw !== '' && $toRaw !== '') {
                $validation = $redirectValidationService->validate($fromRaw, $toRaw, [
                    'existing_redirects' => $graph,
                    'locale' => $locale,
                ]);

                if (! ($validation['valid'] ?? false)) {
                    $errors = array_merge($errors, $validation['errors'] ?? ['Invalid redirect.']);
                }
                $warnings = array_merge($warnings, $validation['warnings'] ?? []);
                $normFrom = $validation['normalized']['from'] ?? $normFrom;
                $normTo = $validation['normalized']['to'] ?? $normTo;
            }

            $valid = $errors === [];

            $action = null;
            if ($valid && $locale !== '' && $normFrom !== '') {
                $dupKey = $locale . '|' . $normFrom;
                if (isset($batchNormKeys[$dupKey])) {
                    $valid = false;
                    $errors[] = 'Duplicate locale + from_path in this file (see line ' . $batchNormKeys[$dupKey] . ').';
                } else {
                    $batchNormKeys[$dupKey] = $line;
                    $existing = $this->firstRedirectMatchingLocaleAndNormalizedFrom($locale, $normFrom, $canonical);
                    $action = $existing ? 'update' : 'create';
                    $priorEdges[$normFrom] = $normTo;
                }
            }

            $result[] = [
                'line' => $line,
                'locale' => $locale,
                'from_path' => $fromRaw,
                'to_path' => $toRaw,
                'status_code' => $statusCode,
                'is_active' => $isActive,
                'normalized' => ['from' => $normFrom, 'to' => $normTo],
                'valid' => $valid,
                'errors' => $errors,
                'warnings' => $warnings,
                'action' => $action,
            ];
        }

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $prepared
     * @return array{created: int, updated: int}
     */
    public function bulkSheetCommitPreparedRows(array $prepared): array
    {
        $canonical = $this->app->make(CanonicalUrlResolverInterface::class);
        $created = 0;
        $updated = 0;

        foreach ($prepared as $row) {
            $fields = [
                'from_path' => $row['normalized']['from'],
                'to_path' => $row['normalized']['to'],
                'locale' => $row['locale'],
                'status_code' => $row['status_code'],
                'is_active' => $row['is_active'],
            ];

            $existing = $this->firstRedirectMatchingLocaleAndNormalizedFrom(
                (string) $row['locale'],
                (string) $row['normalized']['from'],
                $canonical
            );

            if ($existing instanceof Model) {
                $this->repository->update($existing->getKey(), $fields);
                $updated++;
            } else {
                $this->repository->create($fields);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * @param resource $resource
     */
    public function bulkSheetStreamExport($resource): void
    {
        $headers = [];
        foreach ($this->bulkSheetFields() as $field) {
            $aliases = array_values(array_unique(array_merge(
                [$field['key']],
                $field['aliases'] ?? []
            )));
            $headers[] = $aliases[0] ?? $field['key'];
        }
        fputcsv($resource, $headers);

        $this->repository->query()
            ->orderBy('id')
            ->chunk(500, function ($chunk) use ($resource): void {
                foreach ($chunk as $redirect) {
                    /** @var Redirect $redirect */
                    fputcsv($resource, [
                        $redirect->locale,
                        $redirect->from_path,
                        $redirect->to_path,
                        $redirect->status_code,
                        $redirect->is_active ? '1' : '0',
                    ]);
                }
            });
    }

    private function firstRedirectMatchingLocaleAndNormalizedFrom(
        string $locale,
        string $normalizedFrom,
        CanonicalUrlResolverInterface $canonical,
    ): ?Redirect {
        return $this->repository->query()
            ->where('locale', $locale)
            ->get()
            ->first(fn (Redirect $r) => $canonical->normalizePath((string) $r->from_path) === $normalizedFrom);
    }

    /**
     * @return array<string, string>
     */
    protected function bulkSheetInertiaUiStrings(): array
    {
        return [
            'intro' => __('modules.cms.redirect.intro'),
            'headline' => __('modules.cms.redirect.headline'),
            'browser_title' => __('modules.cms.redirect.browser_title'),
        ];
    }

    // /**
    //  * @return list<array{title: string, href?: string, disabled?: bool}>
    //  */
    // protected function bulkSheetBreadcrumbsItems(): array
    // {
    //     $redirectIndex = $this->module->panelRouteNamePrefix() . '.' . snakeCase($this->routeName) . '.index';

    //     $cmsCrumb = [
    //         'title' => __('CMS'),
    //     ];

    //     $redirectCrumb = [
    //         'title' => __('Redirects'),
    //     ];
    //     if (Route::has($redirectIndex)) {
    //         $redirectCrumb['href'] = route($redirectIndex);
    //     }

    //     return [
    //         $cmsCrumb,
    //         $redirectCrumb,
    //         [
    //             'title' => __('Import / export'),
    //         ],
    //     ];
    // }
}
