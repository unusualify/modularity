<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Stubs;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View as ViewContract;
use Inertia\Response as InertiaResponse;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Repositories\Repository;

class BaseControllerStub extends BaseController
{
    protected $moduleName = 'TestModule';

    protected $routeName = 'Item';

    protected $setDefaultPermissions = false;

    protected $formSchema = [];

    public ?Repository $repositoryOverride = null;

    /** @var array<string, mixed> */
    public array $lastIndexData = [];

    /** @var array<string, mixed> */
    public array $lastFormData = [];

    public function __construct(Application $app, Request $request, ?Repository $repositoryOverride = null)
    {
        $this->repositoryOverride = $repositoryOverride;

        parent::__construct($app, $request);
    }

    protected function getNamespace(): string
    {
        return 'TestModules\\TestModule';
    }

    public function getRepository()
    {
        return $this->repositoryOverride ?? parent::getRepository();
    }

    protected function getIndexData($prependScope = [])
    {
        return [
            'module' => $this->moduleName,
            'scopes' => $prependScope,
        ];
    }

    protected function getFormData($id = null): array
    {
        return [
            'id' => $id,
            'fields' => [],
        ];
    }

    protected function renderIndex(array $data): InertiaResponse|ViewContract
    {
        $this->lastIndexData = $data;

        return view()->file($this->testViewPath(), $data);
    }

    protected function renderForm(array $data): InertiaResponse|ViewContract
    {
        $this->lastFormData = $data;

        return view()->file($this->testViewPath(), $data);
    }

    protected function respondWithRedirect($url, $attributes = [])
    {
        return response()->json(array_merge($attributes, [
            'redirector' => $url,
        ]));
    }

    private function testViewPath(): string
    {
        return realpath(__DIR__ . '/../../Support/views/empty.blade.php')
            ?: __DIR__ . '/../../Support/views/empty.blade.php';
    }

    protected function validateFormRequest($schema = [])
    {
        return $this->request;
    }

    protected function getPreviousRouteSchema(): array
    {
        return [];
    }

    protected function getItemIdentifier($item): ?int
    {
        return $item->id ?? null;
    }

    protected function getBackLink($fallback = null, $params = [])
    {
        return '/admin/back';
    }

    public function exposeGetViewPrefix(): ?string
    {
        return $this->getViewPrefix();
    }

    public function exposePreloadBase(): void
    {
        // Mirrors PanelController middleware: user is set before preload() runs.
        // Prefer the Modularous guard (actingAs often targets it); fall back to default.
        $this->user = Auth::guard(Modularous::getAuthGuardName())->user() ?? Auth::user();

        $this->preload();
    }

    public function setIndexOptions(array $indexOptions): void
    {
        $this->indexOptions = $indexOptions;
    }

    public function isInertiaRequest(): bool
    {
        return false;
    }
}
