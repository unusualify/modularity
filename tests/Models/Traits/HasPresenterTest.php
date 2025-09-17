<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasPresenter;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasPresenterTest extends ModelTestCase
{
    protected TestPresenterModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new TestPresenterModel();
    }

    public function test_model_uses_has_presenter_trait()
    {
        $traits = class_uses_recursive($this->model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasPresenter', $traits);
    }

    public function test_present_method_throws_exception_when_presenter_not_set()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please set the Presenter path to your Presenter :presenter FQN');

        $this->model->present();
    }

    public function test_present_method_throws_exception_when_presenter_class_not_exists()
    {
        $this->model->presenter = 'NonExistentPresenterClass';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please set the Presenter path to your Presenter :presenter FQN');

        $this->model->present();
    }

    public function test_present_method_returns_presenter_instance()
    {
        $this->model->presenter = TestPresenter::class;

        $presenter = $this->model->present();

        $this->assertInstanceOf(TestPresenter::class, $presenter);
        $this->assertSame($this->model, $presenter->model);
    }

    public function test_present_method_returns_same_instance_on_subsequent_calls()
    {
        $this->model->presenter = TestPresenter::class;

        $presenter1 = $this->model->present();
        $presenter2 = $this->model->present();

        $this->assertSame($presenter1, $presenter2);
    }

    public function test_present_admin_method_calls_present_with_presenter_admin()
    {
        $this->model->presenterAdmin = TestPresenter::class;

        $presenter = $this->model->presentAdmin();

        $this->assertInstanceOf(TestPresenter::class, $presenter);
    }

    public function test_present_admin_method_throws_exception_when_presenter_admin_not_set()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please set the Presenter path to your Presenter :presenterAdmin FQN');

        $this->model->presentAdmin();
    }

    public function test_set_presenter_method_sets_presenter_property()
    {
        $presenterClass = TestPresenter::class;

        $result = $this->model->setPresenter($presenterClass);

        $this->assertEquals($presenterClass, $this->model->presenter);
        $this->assertSame($this->model, $result);
    }

    public function test_set_presenter_method_does_not_override_existing_presenter()
    {
        $originalPresenter = TestPresenter::class;
        $newPresenter = 'AnotherPresenter';

        $this->model->presenter = $originalPresenter;
        $this->model->setPresenter($newPresenter);

        $this->assertEquals($originalPresenter, $this->model->presenter);
    }

    public function test_set_presenter_method_with_custom_property()
    {
        $presenterClass = TestPresenter::class;

        $result = $this->model->setPresenter($presenterClass, 'customPresenter');

        $this->assertEquals($presenterClass, $this->model->customPresenter);
        $this->assertSame($this->model, $result);
    }

    public function test_set_presenter_admin_method_sets_presenter_admin_property()
    {
        $presenterClass = TestPresenter::class;

        $result = $this->model->setPresenterAdmin($presenterClass);

        $this->assertEquals($presenterClass, $this->model->presenterAdmin);
        $this->assertSame($this->model, $result);
    }

    public function test_set_presenter_admin_method_does_not_override_existing_presenter_admin()
    {
        $originalPresenter = TestPresenter::class;
        $newPresenter = 'AnotherPresenter';

        $this->model->presenterAdmin = $originalPresenter;
        $this->model->setPresenterAdmin($newPresenter);

        $this->assertEquals($originalPresenter, $this->model->presenterAdmin);
    }

    public function test_presenter_instance_property_is_cached()
    {
        $this->model->presenter = TestPresenter::class;

        // First call creates instance
        $presenter1 = $this->model->present();

        // Second call should return cached instance
        $presenter2 = $this->model->present();

        $this->assertSame($presenter1, $presenter2);
    }
}

// Test model that uses the HasPresenter trait
class TestPresenterModel extends Model
{
    use HasPresenter;

    public $presenter;
    public $presenterAdmin;
    public $customPresenter;
}

// Test presenter class
class TestPresenter
{
    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }
}
