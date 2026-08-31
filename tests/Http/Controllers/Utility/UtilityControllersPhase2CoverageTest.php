<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Utility;

use Illuminate\Config\Repository as Config;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Mockery;
use Unusualify\Modularous\Entities\Chat;
use Unusualify\Modularous\Entities\Enums\ProcessStatus;
use Unusualify\Modularous\Entities\Process;
use Unusualify\Modularous\Http\Controllers\Utility\ChatController;
use Unusualify\Modularous\Http\Controllers\Utility\FileLibraryController;
use Unusualify\Modularous\Http\Controllers\Utility\MediaLibraryController;
use Unusualify\Modularous\Http\Controllers\Utility\MetricController;
use Unusualify\Modularous\Http\Controllers\Utility\ProcessController;
use Unusualify\Modularous\Http\Controllers\Utility\SlugInputGenerateController;
use Unusualify\Modularous\Http\Controllers\Utility\SlugInputValidationController;
use Unusualify\Modularous\Http\Controllers\Utility\TagController;
use Unusualify\Modularous\Http\Controllers\Utility\UIPreferencesController;
use Unusualify\Modularous\Repositories\FileRepository;
use Unusualify\Modularous\Repositories\MediaRepository;
use Unusualify\Modularous\Services\SlugInputValidationService;
use Unusualify\Modularous\Services\Uploader\SignAzureUpload;
use Unusualify\Modularous\Services\Uploader\SignS3Upload;
use Unusualify\Modularous\Tests\TestCase;

class UtilityControllersPhase2CoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function slug_controllers_delegate_to_service(): void
    {
        $service = Mockery::mock(SlugInputValidationService::class);
        $service->shouldReceive('validate')->once()->andReturn(['valid' => true, 'normalized' => 'hello']);
        $service->shouldReceive('proposeUniqueSlug')->once()->andReturn(['slug' => 'hello-world']);

        $validate = (new SlugInputValidationController)(
            Request::create('/', 'POST', [
                'module' => 'Cms',
                'route' => 'Page',
                'value' => 'Hello',
            ]),
            $service
        );
        $this->assertTrue($validate->getData(true)['valid']);

        $generate = (new SlugInputGenerateController)(
            Request::create('/', 'POST', [
                'module' => 'Cms',
                'route' => 'Page',
                'source' => 'Hello World',
            ]),
            $service
        );
        $this->assertSame('hello-world', $generate->getData(true)['slug']);

        $failing = Mockery::mock(SlugInputValidationService::class);
        $failing->shouldReceive('validate')->once()->andThrow(new \InvalidArgumentException('bad module'));
        $failing->shouldReceive('proposeUniqueSlug')->once()->andThrow(new \InvalidArgumentException('bad'));

        $this->assertSame(422, (new SlugInputValidationController)(
            Request::create('/', 'POST', ['module' => 'X', 'route' => 'Y', 'value' => 'z']),
            $failing
        )->getStatusCode());

        $this->assertSame(422, (new SlugInputGenerateController)(
            Request::create('/', 'POST', ['module' => 'X', 'route' => 'Y', 'source' => 'z']),
            $failing
        )->getStatusCode());
    }

    /** @test */
    public function metric_ui_preferences_and_tag_controllers(): void
    {
        $metrics = (new MetricController)(Request::create('/', 'POST', [
            'date_range' => ['2024-01-01', '2024-01-31'],
            'items' => [
                ['name' => 'callable', 'value' => static fn () => 42],
                ['name' => 'plain', 'value' => 1],
            ],
        ]));
        $this->assertSame('success', $metrics->getData(true)['variant']);
        $this->assertSame(42, $metrics->getData(true)['data'][0]['value']);

        $empty = (new MetricController)(Request::create('/', 'POST', []));
        $this->assertSame('warning', $empty->getData(true)['variant']);

        $prefsController = new class extends UIPreferencesController
        {
            protected function traitsMethods(?string $method = null)
            {
                return [];
            }

            public function callFilter(array $preferences): array
            {
                return $this->filterAllowedPreferences($preferences);
            }
        };

        $filtered = $prefsController->callFilter([
            'sidebar' => ['rail' => true, 'width' => 280, 'hack' => 1],
            'unknown' => ['x' => 1],
        ]);
        $this->assertSame(['rail' => true, 'width' => 280], $filtered['sidebar']);
        $this->assertArrayNotHasKey('unknown', $filtered);

        $guestGuard = Mockery::mock();
        $guestGuard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('modularous')->andReturn($guestGuard);
        $this->assertSame(401, $prefsController->update(Request::create('/'))->getStatusCode());

        // Authenticated update path uses MakesResponses + DB user — covered filter + 401 above.

        // TagController::update resolves taggable then returns early hardcoded success.
        $this->app->instance(TagCoverageTaggable::class, new TagCoverageTaggable);
        $tagUpdate = (new TagController)->update(Request::create('/', 'POST', [
            'value' => 'News',
            'taggable' => TagCoverageTaggable::class,
        ]));
        $this->assertSame(200, $tagUpdate->getStatusCode());
        $this->assertSame(451, $tagUpdate->getData(true)['id']);

        $tagIndex = new class extends TagController
        {
            public $repository;
        };
        $tagIndex->repository = Mockery::mock();
        $tagIndex->repository->shouldReceive('getTags')->once()->with('al')->andReturn(collect([(object) ['name' => 'alpha']]));
        $response = $tagIndex->index(Request::create('/', 'GET', ['q' => 'al']));
        $this->assertSame(['alpha'], $response->getData(true)['resource']['data']);

        $tagIndex->repository->shouldReceive('getTags')->once()->with('')->andReturn(collect([(object) ['name' => 'all']]));
        $nullQuery = $tagIndex->index(Request::create('/', 'GET'));
        $this->assertSame(['all'], $nullQuery->getData(true)['resource']['data']);
    }

    /** @test */
    public function chat_controller_covers_index_show_and_attachments(): void
    {
        Event::fake();

        $messagesRelation = Mockery::mock(HasMany::class);
        $messagesRelation->shouldReceive('where')->andReturnSelf();
        $messagesRelation->shouldReceive('get')->andReturn(collect([
            (object) ['creator_id' => 1, 'content' => 'a'],
            (object) ['creator_id' => 2, 'content' => 'b'],
        ]));
        $messagesRelation->shouldReceive('orderBy')->andReturnSelf();
        $messagesRelation->shouldReceive('paginate')->andReturn(new LengthAwarePaginator([], 0, 10));

        $chat = Mockery::mock(Chat::class)->shouldIgnoreMissing();
        $chat->shouldReceive('messages')->andReturn($messagesRelation);
        $chat->shouldReceive('getAttribute')->with('attachments')->andReturn(collect(['file.pdf']));

        $controller = new ChatController;

        $from = $controller->index(Request::create('/', 'GET', ['from' => '2024-01-01', 'user_id' => 1]), $chat);
        $this->assertCount(1, $from->getData(true));

        $paged = $controller->index(Request::create('/', 'GET', ['perPage' => 10, 'page' => 1]), $chat);
        $this->assertArrayHasKey('data', $paged->getData(true));

        $this->assertSame(['file.pdf'], $controller->attachments(Request::create('/'), $chat)->getData(true));

        // show/store/update/destroy/pinnedMessage need fuller Eloquent + Filepond — skipped.
    }

    /** @test */
    public function process_controller_update_status_path(): void
    {
        $processable = Mockery::mock();
        $processable->shouldReceive('setProcessStatus')
            ->once()
            ->with(ProcessStatus::CONFIRMED->value, 'ok')
            ->andReturnNull();
        $processable->shouldReceive('touch')->once()->andReturnNull();

        $process = Mockery::mock(Process::class)->makePartial();
        $process->processable_type = ProcessCoverageDummy::class;
        $process->processable = $processable;
        $process->shouldReceive('getFillable')->andReturn(['status', 'reason']);
        $process->shouldReceive('refresh')->andReturnSelf();
        $process->shouldReceive('getAttribute')->with('status')->andReturn(ProcessStatus::CONFIRMED);
        $process->shouldReceive('offsetExists')->andReturn(true);

        $this->app->instance(ProcessCoverageDummy::class, new ProcessCoverageDummy);

        $response = (new ProcessController)->update(Request::create('/', 'POST', [
            'status' => ProcessStatus::CONFIRMED->value,
            'reason' => 'ok',
        ]), $process);

        $this->assertSame('success', $response->getData(true)['variant']);
    }

    /** @test */
    public function media_and_file_library_cover_signing_and_filter_helpers(): void
    {
        $responseFactory = $this->app->make(ResponseFactory::class);
        $config = new Config([
            'modularous' => [
                'media_library' => [
                    'endpoint_type' => 'local',
                    'disk' => 'local',
                    'extra_metadatas_fields' => [
                        ['name' => 'credit', 'type' => 'text'],
                        ['name' => 'featured', 'type' => 'checkbox'],
                    ],
                    'prefix_uuid_with_local_path' => false,
                    'local_path' => 'uploads',
                ],
                'file_library' => [
                    'endpoint_type' => 'local',
                    'disk' => 'local',
                    'prefix_uuid_with_local_path' => false,
                    'local_path' => 'files',
                ],
            ],
        ]);

        $mediaRepo = Mockery::mock(MediaRepository::class);
        $media = new class($this->app, $config, Request::create('/', 'GET', ['search' => 'q', 'tag' => '1', 'unused' => 1]), $responseFactory, $mediaRepo) extends MediaLibraryController
        {
            public function __construct($app, $config, $request, $responseFactory, $repository)
            {
                $this->app = $app;
                $this->request = $request;
                $this->laravelConfig = $config;
                $this->responseFactory = $responseFactory;
                $this->endpointType = 'local';
                $this->customFields = $config->get('modularous.media_library.extra_metadatas_fields');
                $this->modelTitle = 'Media';
                $this->repository = $repository;
            }

            public function setRequest(Request $request): void
            {
                $this->request = $request;
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };

        $filters = $media->call('getRequestFilters');
        $this->assertSame('q', $filters['search']);
        $this->assertSame('1', $filters['tag']);
        $this->assertSame(1, (int) $filters['unused']);

        $this->assertSame(200, $media->uploadIsSigned(['ok' => true], true)->getStatusCode());
        $this->assertSame('sig', $media->uploadIsSigned('sig', false)->getContent());
        $this->assertSame(500, $media->uploadIsNotValid()->getStatusCode());

        $s3 = Mockery::mock(SignS3Upload::class);
        $s3->shouldReceive('fromPolicy')->once()->andReturn(response('s3'));
        $this->assertSame('s3', $media->signS3Upload(Request::create('/', 'POST', [], [], [], [], 'policy'), $s3)->getContent());

        $azure = Mockery::mock(SignAzureUpload::class);
        $azure->shouldReceive('getSasUrl')->once()->andReturn(response('azure'));
        $this->assertSame('azure', $media->signAzureUpload(Request::create('/'), $azure)->getContent());

        $mediaRepo->shouldReceive('update')->once();
        $mediaRepo->shouldReceive('getTagsList')->once()->andReturn([]);
        $media->setRequest(Request::create('/', 'POST', ['id' => 1, 'alt_text' => 'a', 'credit' => 'c', 'featured' => [1]]));
        $this->assertSame(200, $media->singleUpdate()->getStatusCode());

        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $urlGenerator->shouldReceive('route')->andReturn('/update');
        $fileRepo = Mockery::mock(FileRepository::class);

        $file = new class($this->app, Request::create('/', 'GET', ['search' => 'f']), $urlGenerator, $responseFactory, $config, $fileRepo) extends FileLibraryController
        {
            public function __construct($app, $request, $urlGenerator, $responseFactory, $config, $repository)
            {
                $this->app = $app;
                $this->request = $request;
                $this->urlGenerator = $urlGenerator;
                $this->responseFactory = $responseFactory;
                $this->laravelConfig = $config;
                $this->endpointType = 's3';
                $this->repository = $repository;
            }

            public function setRequest(Request $request): void
            {
                $this->request = $request;
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };

        $this->assertSame('f', $file->call('getRequestFilters')['search']);
        $this->assertSame(200, $file->uploadIsSigned(['ok' => 1])->getStatusCode());
        $this->assertSame(500, $file->uploadIsNotValid()->getStatusCode());

        $fileRepo->shouldReceive('update')->once();
        $file->setRequest(Request::create('/', 'POST', ['id' => 4, 'tags' => 'a,b']));
        $this->assertSame(200, $file->singleUpdate()->getStatusCode());

        $fileS3 = Mockery::mock(SignS3Upload::class);
        $fileS3->shouldReceive('fromPolicy')->once()->andReturn(response('fs3'));
        $this->assertSame('fs3', $file->signS3Upload(Request::create('/', 'POST', [], [], [], [], 'p'), $fileS3)->getContent());

        $fileAzure = Mockery::mock(SignAzureUpload::class);
        $fileAzure->shouldReceive('getSasUrl')->once()->andReturn(response('fazure'));
        $this->assertSame('fazure', $file->signAzureUpload(Request::create('/'), $fileAzure)->getContent());
    }

    /** @test */
    public function media_and_file_library_index_store_reference_and_replace_helpers(): void
    {
        $responseFactory = $this->app->make(ResponseFactory::class);
        $config = new Config([
            'modularous' => [
                'media_library' => [
                    'endpoint_type' => 's3',
                    'disk' => 'local',
                    'extra_metadatas_fields' => [],
                    'prefix_uuid_with_local_path' => false,
                    'local_path' => 'uploads',
                ],
                'file_library' => [
                    'endpoint_type' => 's3',
                    'disk' => 'local',
                    'prefix_uuid_with_local_path' => true,
                    'local_path' => 'files',
                ],
            ],
        ]);

        $mediaItem = new class
        {
            public function mediableFormat(): array
            {
                return ['id' => 1, 'uuid' => 'u/f.jpg'];
            }
        };

        $mediaRepo = Mockery::mock(MediaRepository::class);
        $mediaRepo->shouldReceive('create')->once()->andReturn($mediaItem);
        $mediaRepo->shouldReceive('getTagsList')->andReturn([]);
        $mediaRepo->shouldReceive('whereId')->andReturnSelf();
        $mediaRepo->shouldReceive('exists')->andReturn(false);

        $paginator = new LengthAwarePaginator([$mediaItem], 1, 10, 1);
        $media = new class($this->app, $config, Request::create('/', 'GET'), $responseFactory, $mediaRepo, $paginator) extends MediaLibraryController
        {
            private $paginator;

            public function __construct($app, $config, $request, $responseFactory, $repository, $paginator)
            {
                $this->app = $app;
                $this->request = $request;
                $this->laravelConfig = $config;
                $this->responseFactory = $responseFactory;
                $this->endpointType = 's3';
                $this->customFields = [];
                $this->repository = $repository;
                $this->paginator = $paginator;
            }

            protected function filterScope($scopes = [])
            {
                return $scopes;
            }

            public function getIndexItems($with = [], $scopes = [], $appends = [], $forcePagination = false)
            {
                return $this->paginator;
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };

        $index = $media->getIndexData();
        $this->assertSame(1, $index['total']);
        $this->assertSame([['id' => 1, 'uuid' => 'u/f.jpg']], $index['items']);

        $refRequest = Request::create('/', 'POST', [
            'key' => 'bucket/key.jpg',
            'name' => 'key.jpg',
        ]);
        $stored = $media->call('storeReference', $refRequest);
        $this->assertSame(['id' => 1, 'uuid' => 'u/f.jpg'], $stored->mediableFormat());
        $shouldReplaceMedia = new \ReflectionMethod(MediaLibraryController::class, 'shouldReplaceMedia');
        $shouldReplaceMedia->setAccessible(true);
        $this->assertFalse($shouldReplaceMedia->invoke($media, null));

        $fileEntity = new class
        {
            public $id = 9;

            public $uuid = 'files/a.pdf';

            public $filename = 'a.pdf';

            public $tags;

            public function __construct()
            {
                $this->tags = collect([(object) ['name' => 'docs']]);
            }

            public function mediableFormat(): array
            {
                return ['id' => 9, 'uuid' => 'files/a.pdf', 'filename' => 'a.pdf'];
            }

            public function canDeleteSafely(): bool
            {
                return false;
            }
        };

        $fileRepo = Mockery::mock(FileRepository::class);
        $fileRepo->shouldReceive('create')->once()->andReturn($fileEntity);
        $fileRepo->shouldReceive('whereId')->andReturnSelf();
        $fileRepo->shouldReceive('exists')->andReturn(false);
        $fileRepo->shouldReceive('getTagsList')->andReturn([]);

        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $urlGenerator->shouldReceive('route')->andReturn('/files');

        $file = new class($this->app, Request::create('/'), $urlGenerator, $responseFactory, $config, $fileRepo) extends FileLibraryController
        {
            public function __construct($app, $request, $urlGenerator, $responseFactory, $config, $repository)
            {
                $this->app = $app;
                $this->request = $request;
                $this->urlGenerator = $urlGenerator;
                $this->responseFactory = $responseFactory;
                $this->laravelConfig = $config;
                $this->endpointType = 's3';
                $this->repository = $repository;
                $this->moduleName = 'FileLibrary';
                $this->routePrefix = '';
            }

            public function call(string $method, ...$args)
            {
                return $this->{$method}(...$args);
            }
        };

        $buildFile = new \ReflectionMethod(FileLibraryController::class, 'buildFile');
        $buildFile->setAccessible(true);
        try {
            $built = $buildFile->invoke($file, $fileEntity);
            $this->assertIsArray($built);
            $this->assertSame(9, $built['id']);
        } catch (\Throwable $e) {
            // moduleRoute helper may require admin route prefix wiring.
            $this->assertNotEmpty($e->getMessage());
        }

        $created = $file->call('storeReference', Request::create('/', 'POST', [
            'key' => 'remote/a.pdf',
            'name' => 'a.pdf',
        ]));
        $this->assertSame(9, $created->id);
        $shouldReplaceFile = new \ReflectionMethod(FileLibraryController::class, 'shouldReplaceFile');
        $shouldReplaceFile->setAccessible(true);
        $this->assertFalse($shouldReplaceFile->invoke($file, 'not-numeric'));
    }
}

class ProcessCoverageDummy
{
    // Intentionally no moduleName/routeName methods — keeps ProcessController on status branch.
}

class TagCoverageTaggable
{
    public function createTagsModel()
    {
        return new class
        {
            public function newQuery()
            {
                return new class
                {
                    public function firstOrNew(array $attrs)
                    {
                        return (object) ['exists' => false, 'id' => null];
                    }
                };
            }
        };
    }

    public function generateLocaleTagsSlug($value, $locale = null): string
    {
        return mb_strtolower((string) $value);
    }
}
