<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Logic;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Repositories\Logic\TouchableEloquentModel;
use Unusualify\Modularous\Tests\TestCase;

class TouchableEloquentModelTest extends TestCase
{
    public function test_touch_eloquent_model_when_flagged(): void
    {
        $repository = new class
        {
            use TouchableEloquentModel;
        };

        $model = new class extends Model
        {
            public bool $touched = false;

            public function touch($attribute = null): bool
            {
                $this->touched = true;

                return true;
            }
        };

        $repository->mustTouchEloquentModel();
        $repository->touchEloquentModel($model);

        $this->assertTrue($model->touched);
    }

    public function test_touch_eloquent_model_when_object_requires_touch(): void
    {
        $repository = new class
        {
            use TouchableEloquentModel;
        };

        $model = new class extends Model
        {
            public bool $mustTouchable = true;

            public bool $touched = false;

            public function touch($attribute = null): bool
            {
                $this->touched = true;

                return true;
            }
        };

        $repository->touchEloquentModel($model);

        $this->assertTrue($model->touched);
    }

    public function test_let_eloquent_model_be_touched_sets_flag(): void
    {
        $repository = new class
        {
            use TouchableEloquentModel;

            public function isMustTouch(): bool
            {
                return $this->mustTouchEloquentModel;
            }
        };

        $repository->letEloquentModelBeTouched(true);

        $this->assertTrue($repository->isMustTouch());
    }
}
