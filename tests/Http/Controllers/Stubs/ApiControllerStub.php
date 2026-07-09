<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Stubs;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\ApiController;
use Unusualify\Modularous\Repositories\Repository;

class ApiControllerStub extends ApiController
{
    protected $moduleName = 'TestModule';

    protected $routeName = 'Item';

    public ?Repository $repositoryOverride = null;

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

    protected function validateFormRequest($schema = [])
    {
        return $this->request;
    }

    public function exposeGetPerPage(): int
    {
        return $this->getPerPage();
    }

    /**
     * @return array<int, string>
     */
    public function exposeGetIncludes(): array
    {
        return $this->getIncludes();
    }

    /**
     * @return array<int, string>
     */
    public function exposeGetIncludesForEagerLoading(): array
    {
        return $this->getIncludesForEagerLoading();
    }

    public function exposeRespondWithData(mixed $data, int $status = 200)
    {
        return $this->respondWithData($data, $status);
    }

    public function exposeGetApiResourceClass(): ?string
    {
        return $this->getApiResourceClass();
    }

    public function configureResponse(bool $wrapResponses, array $metadata = []): void
    {
        $this->wrapResponses = $wrapResponses;
        $this->responseMetadata = $metadata;
    }

    public function configureIncludes(array $defaultIncludes, array $availableIncludes): void
    {
        $this->defaultIncludes = $defaultIncludes;
        $this->availableIncludes = $availableIncludes;
    }

    public function exposeApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function exposeDefaultPerPage(): int
    {
        return $this->defaultPerPage;
    }
}
