<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Coverage;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Unusualify\Modularous\Entities\Feature;
use Unusualify\Modularous\Entities\Setting;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\Traits\IsTranslatable;
use Unusualify\Modularous\Entities\Traits\Secondary\HasRelation;
use Unusualify\Modularous\Entities\Translations\SettingTranslation;
use Unusualify\Modularous\Facades\Coverage;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ValidationException as ValidationExceptionFacade;
use Unusualify\Modularous\Http\Middleware\CompanyRegistrationMiddleware;
use Unusualify\Modularous\Http\Requests\FileRequest;
use Unusualify\Modularous\Http\Requests\OauthRequest;
use Unusualify\Modularous\Http\Requests\StorePermissionRequest;
use Unusualify\Modularous\Http\Requests\StoreRoleRequest;
use Unusualify\Modularous\Http\ViewComposers\Localization;
use Unusualify\Modularous\Hydrates\Inputs\BrowserHydrate;
use Unusualify\Modularous\Hydrates\Inputs\ChatHydrate;
use Unusualify\Modularous\Hydrates\Inputs\ComboboxHydrate;
use Unusualify\Modularous\Hydrates\Inputs\CreatorHydrate;
use Unusualify\Modularous\Hydrates\Inputs\FormTabsHydrate;
use Unusualify\Modularous\Hydrates\Inputs\ImageGalleryHydrate;
use Unusualify\Modularous\Hydrates\Inputs\ModuleRouteModelHydrate;
use Unusualify\Modularous\Hydrates\Inputs\OtpInputHydrate;
use Unusualify\Modularous\Hydrates\Inputs\RelationshipsHydrate;
use Unusualify\Modularous\Hydrates\Inputs\SelectHydrate;
use Unusualify\Modularous\Notifications\LoginMfaCodeNotification;
use Unusualify\Modularous\Observers\RemoteApiSourceObserver;
use Unusualify\Modularous\Services\CoverageService;
use Unusualify\Modularous\Services\MediaLibrary\Glide;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Batch flips for near-zero / tiny classes to push class coverage past 50%.
 */
class Phase1ClassFlipCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function coverage_and_validation_exception_facades_resolve(): void
    {
        $service = Mockery::mock(CoverageService::class);
        $service->shouldReceive('hasErrors')->once()->andReturn(false);
        $this->app->instance('coverage.service', $service);

        $this->assertFalse(Coverage::hasErrors());
        $this->assertInstanceOf(CoverageService::class, Coverage::getFacadeRoot());

        $exception = ValidationExceptionFacade::withMessages(['field' => ['invalid']]);
        $this->assertInstanceOf(\Unusualify\Modularous\Exceptions\ValidationException::class, $exception);
    }

    /** @test */
    public function glide_controller_delegates_to_glide_service(): void
    {
        // File lives under Utility/ but namespace omits Utility — load explicitly for PSR-4.
        $glideControllerPath = dirname(__DIR__, 2) . '/src/Http/Controllers/Utility/GlideController.php';
        require_once $glideControllerPath;

        $glide = Mockery::mock(Glide::class);
        $glide->shouldReceive('render')->once()->with('photo.jpg')->andReturn(response('img-bytes'));
        $this->app->instance(Glide::class, $glide);

        $controller = new \Unusualify\Modularous\Http\Controllers\GlideController;
        $response = $controller('photo.jpg', $this->app);

        $this->assertSame('img-bytes', $response->getContent());
    }

    /** @test */
    public function company_registration_middleware_is_passthrough(): void
    {
        $middleware = $this->app->make(CompanyRegistrationMiddleware::class);
        $response = $middleware->handle(Request::create('/'), static fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    /** @test */
    public function store_permission_and_role_requests(): void
    {
        $permission = new StorePermissionRequest;
        $this->assertTrue($permission->authorize());
        $this->assertSame('required|unique:permissions', $permission->rules()['name']);

        $role = new StoreRoleRequest;
        $this->assertTrue($role->authorize());
        $this->assertSame('required|unique:roles|min:4', $role->rules()['name']);
    }

    /** @test */
    public function file_request_rules_by_endpoint_type(): void
    {
        config(['modularous.file_library.endpoint_type' => 'local']);
        $this->assertArrayHasKey('qqfile', (new FileRequest)->rules());

        config(['modularous.file_library.endpoint_type' => 'azure']);
        $this->assertArrayHasKey('blob', (new FileRequest)->rules());

        config(['modularous.file_library.endpoint_type' => 's3']);
        $this->assertArrayHasKey('key', (new FileRequest)->rules());
    }

    /** @test */
    public function oauth_request_all_and_rules(): void
    {
        config(['modularous.oauth.providers' => ['google' => [], 'github' => []]]);

        $base = Request::create('/oauth/google', 'GET', ['provider' => 'google']);
        $request = OauthRequest::createFrom($base, new OauthRequest);

        $this->assertSame('google', $request->all()['provider']);
        $this->assertArrayHasKey('provider', $request->rules());
    }

    /** @test */
    public function localization_composer_shares_config(): void
    {
        $captured = null;
        $view = Mockery::mock(ViewContract::class);
        $view->shouldReceive('with')->once()->with(Mockery::on(function (array $data) use (&$captured) {
            $captured = $data;

            return array_key_exists('modularousLocalization', $data);
        }))->andReturnSelf();

        (new Localization)->compose($view);

        $this->assertIsArray($captured);
        $this->assertArrayHasKey('modularousLocalization', $captured);
    }

    /** @test */
    public function login_mfa_code_notification(): void
    {
        $notification = new LoginMfaCodeNotification('424242', now()->addMinutes(10));

        $this->assertSame(['mail'], $notification->via((object) []));
        $mail = $notification->toMail((object) []);
        $this->assertNotEmpty($mail->subject);
    }

    /** @test */
    public function setting_translation_and_setting_model_tables(): void
    {
        config(['modularous.settings_table' => 'twill_settings']);

        $this->assertSame('twill_setting_translations', (new SettingTranslation)->getTable());
        $this->assertSame('twill_settings', (new Setting)->getTable());
        $this->assertSame(
            SettingTranslation::class,
            (new Setting)->getTranslationModelNameDefault()
        );
    }

    /** @test */
    public function feature_model_table_featured_and_scope(): void
    {
        config(['modularous.features_table' => 'twill_features']);

        $feature = new Feature;
        $this->assertSame('twill_features', $feature->getTable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $feature->featured());

        Schema::create('twill_features', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('featured');
            $table->integer('position')->default(0);
            $table->string('bucket_key')->nullable();
            $table->boolean('starred')->default(false);
        });

        $this->assertCount(0, Feature::query()->forBucket('missing'));
    }

    /** @test */
    public function is_translatable_trait_paths(): void
    {
        $plain = new class
        {
            use IsTranslatable;
        };
        $this->assertFalse($plain->isTranslatable());
        $this->assertFalse($plain->isTranslatable(['title']));

        $translated = new class
        {
            use HasTranslation;
            use IsTranslatable;

            public $translatedAttributes = ['title', 'body'];
        };

        $this->assertTrue($translated->isTranslatable());
        $this->assertTrue($translated->isTranslatable('title'));
        $this->assertFalse($translated->isTranslatable('missing'));
    }

    /** @test */
    public function has_relation_boots_force_deleting_callback(): void
    {
        Schema::create('has_relation_tmp', function (Blueprint $table) {
            $table->id();
            $table->softDeletes();
        });

        $model = new class extends Model
        {
            use HasRelation;
            use SoftDeletes;

            protected $table = 'has_relation_tmp';

            public $timestamps = false;

            protected $guarded = [];
        };

        $row = $model::query()->create();
        $row->forceDelete();

        $this->assertDatabaseMissing('has_relation_tmp', ['id' => $row->id]);
    }

    /** @test */
    public function remote_api_source_observer_touches_existing_sourceable(): void
    {
        $sourceable = Mockery::mock(Model::class)->makePartial();
        $sourceable->exists = true;
        $sourceable->shouldReceive('touchQuietly')->once()->andReturn(true);

        $source = Mockery::mock(\Unusualify\Modularous\Entities\RemoteApiSource::class)->makePartial();
        $source->shouldReceive('getAttribute')->with('sourceable')->andReturn($sourceable);

        (new RemoteApiSourceObserver)->saved($source);

        $this->assertTrue($sourceable->exists);
    }

    /** @test */
    public function remote_api_source_observer_skips_missing_sourceable(): void
    {
        $source = Mockery::mock(\Unusualify\Modularous\Entities\RemoteApiSource::class)->makePartial();
        $source->shouldReceive('getAttribute')->with('sourceable')->andReturn(null);

        (new RemoteApiSourceObserver)->saved($source);
        $this->assertTrue(true);
    }

    /** @test */
    public function otp_and_image_gallery_and_relationships_hydrate(): void
    {
        $otp = (new OtpInputHydrate(['type' => 'otp', 'name' => 'code'], null, null, true))->render();
        $this->assertSame('otp-input', $otp['type']);
        $this->assertSame(6, $otp['length']);

        $gallery = (new ImageGalleryHydrate(['type' => 'image-gallery', 'name' => 'images'], null, null, true))->render();
        $this->assertSame('input-image-gallery', $gallery['type']);

        $relationships = (new RelationshipsHydrate(['type' => 'relationships', 'name' => 'rels'], null, null, true))->render();
        $this->assertSame('relationships', $relationships['type']);
        $this->assertSame('grey', $relationships['color']);
    }

    /** @test */
    public function browser_combobox_and_select_hydrate_branches(): void
    {
        $browser = (new BrowserHydrate(['name' => 'browser'], null, null, true))->render();
        $this->assertSame('input-browser', $browser['type']);

        $comboboxItems = (new ComboboxHydrate([
            'type' => 'combobox',
            'name' => 'tags',
            'items' => [['id' => 1, 'name' => 'A']],
            'default' => [1],
            'multiple' => true,
        ], null, null, true))->render();
        $this->assertSame([['id' => 1, 'name' => 'A']], $comboboxItems['items']);

        $comboboxScroll = (new ComboboxHydrate([
            'type' => 'combobox',
            'name' => 'tags',
            'ext' => 'scroll',
            'endpoint' => '/api/tags',
            'default' => null,
        ], null, null, true))->render();
        $this->assertSame('input-select-scroll', $comboboxScroll['type']);
        $this->assertSame('v-combobox', $comboboxScroll['componentType']);

        $selectScroll = (new SelectHydrate([
            'type' => 'select',
            'name' => 'category',
            'ext' => 'scroll',
            'endpoint' => '/api/categories',
            'default' => null,
        ], null, null, true))->render();
        $this->assertSame('input-select-scroll', $selectScroll['type']);
        $this->assertSame('v-select', $selectScroll['componentType']);
    }

    /** @test */
    public function module_route_model_hydrate_loads_items(): void
    {
        Modularous::shouldReceive('getModuleRouteModelSelectItems')
            ->once()
            ->with(false, false)
            ->andReturn([
                ['value' => 'App\\Models\\Page', 'title' => 'Cms - page'],
            ]);

        $result = (new ModuleRouteModelHydrate([
            'type' => 'module-route-model',
            'name' => 'model',
            'default' => null,
        ], null, null, true))->render();

        $this->assertSame('select', $result['type']);
        $this->assertSame('value', $result['itemValue']);
        $this->assertNotEmpty($result['items']);
    }

    /** @test */
    public function form_tabs_skips_no_eager_entries(): void
    {
        $result = (new FormTabsHydrate([
            'type' => 'form-tabs',
            'name' => 'tabs',
            'schema' => [
                [
                    'type' => 'select',
                    'name' => 'skipped',
                    'noEager' => true,
                ],
                [
                    'type' => 'autocomplete',
                    'name' => 'kept',
                    'eager' => ['parent'],
                ],
            ],
        ], null, null, true))->render();

        $this->assertContains('parent', $result['eagers']);
        $this->assertNotContains('tabs.skipped', $result['eagers']);
    }

    /** @test */
    public function creator_hydrate_sets_browser_endpoint(): void
    {
        RouteFacade::get('/admin/users', static fn () => 'ok')->name('admin.system.user.index');
        RouteFacade::getRoutes()->refreshNameLookups();

        $result = (new CreatorHydrate([
            'type' => 'creator',
            'name' => 'created_by',
        ], null, null, true))->render();

        $this->assertSame('input-browser', $result['type']);
        $this->assertSame('custom_creator_id', $result['name']);
        $this->assertNotEmpty($result['endpoint']);
        $this->assertArrayNotHasKey('appends', $result);
    }

    /** @test */
    public function chat_hydrate_builds_endpoints_and_filepond(): void
    {
        RouteFacade::get('/chat/{chat}', static fn () => 'ok')->name('admin.chatable.index');
        RouteFacade::post('/chat/{chat}', static fn () => 'ok')->name('admin.chatable.store');
        RouteFacade::get('/chat-message/{chat_message}', static fn () => 'ok')->name('admin.chatable.show');
        RouteFacade::put('/chat-message/{chat_message}', static fn () => 'ok')->name('admin.chatable.update');
        RouteFacade::delete('/chat-message/{chat_message}', static fn () => 'ok')->name('admin.chatable.destroy');
        RouteFacade::get('/chat/{chat}/attachments', static fn () => 'ok')->name('admin.chatable.attachments');
        RouteFacade::get('/chat/{chat}/pinned', static fn () => 'ok')->name('admin.chatable.pinned-message');
        RouteFacade::getRoutes()->refreshNameLookups();

        $result = (new ChatHydrate([
            'type' => 'chat',
            'name' => 'chat',
            'acceptedExtensions' => ['pdf', 'doc'],
            'max-attachments' => 2,
        ], null, null, true))->render();

        $this->assertSame('input-chat', $result['type']);
        $this->assertSame('_chat_id', $result['name']);
        $this->assertArrayHasKey('endpoints', $result);
        $this->assertArrayHasKey('filepond', $result);
        $this->assertTrue($result['noSubmit']);
    }
}
