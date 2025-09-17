<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Tests\ModelTestCase;

class IsTranslatableTest extends ModelTestCase
{
    public function test_model_uses_is_translatable_trait()
    {
        $model = new TestTranslatableModel();
        $traits = class_uses_recursive($model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\IsTranslatable', $traits);
    }

    public function test_is_translatable_returns_false_when_model_does_not_have_has_translation_trait()
    {
        $model = new TestNonTranslatableModel();

        $this->assertFalse($model->isTranslatable());
    }

    public function test_is_translatable_returns_false_when_model_has_trait_but_no_translated_attributes_property()
    {
        $model = new TestTranslatableWithoutAttributesModel();

        $this->assertFalse($model->isTranslatable());
    }

    public function test_is_translatable_returns_false_when_translated_attributes_is_empty()
    {
        $model = new TestTranslatableEmptyAttributesModel();

        $this->assertFalse($model->isTranslatable());
    }

    public function test_is_translatable_returns_true_when_model_has_trait_and_translated_attributes()
    {
        $model = new TestTranslatableModel();

        $this->assertTrue($model->isTranslatable());
    }

    public function test_is_translatable_with_specific_columns_returns_true_when_columns_intersect()
    {
        $model = new TestTranslatableModel();

        // Test with single column that exists
        $this->assertTrue($model->isTranslatable('title'));
        $this->assertTrue($model->isTranslatable(['title']));

        // Test with multiple columns where some exist
        $this->assertTrue($model->isTranslatable(['title', 'non_existent']));

        // Test with all existing columns
        $this->assertTrue($model->isTranslatable(['title', 'description']));
    }

    public function test_is_translatable_with_specific_columns_returns_false_when_no_intersection()
    {
        $model = new TestTranslatableModel();

        // Test with single column that doesn't exist
        $this->assertFalse($model->isTranslatable('non_existent'));
        $this->assertFalse($model->isTranslatable(['non_existent']));

        // Test with multiple columns that don't exist
        $this->assertFalse($model->isTranslatable(['non_existent1', 'non_existent2']));
    }

    public function test_is_translatable_with_string_column_parameter()
    {
        $model = new TestTranslatableModel();

        $this->assertTrue($model->isTranslatable('title'));
        $this->assertTrue($model->isTranslatable('description'));
        $this->assertFalse($model->isTranslatable('non_existent'));
    }

    public function test_is_translatable_with_array_column_parameter()
    {
        $model = new TestTranslatableModel();

        $this->assertTrue($model->isTranslatable(['title']));
        $this->assertTrue($model->isTranslatable(['description']));
        $this->assertTrue($model->isTranslatable(['title', 'description']));
        $this->assertFalse($model->isTranslatable(['non_existent']));
    }

    public function test_is_translatable_with_null_columns_checks_all_translated_attributes()
    {
        $model = new TestTranslatableModel();

        $this->assertTrue($model->isTranslatable(null));
        $this->assertTrue($model->isTranslatable());
    }

    public function test_is_translatable_with_empty_array_checks_all_translated_attributes()
    {
        $model = new TestTranslatableModel();

        $this->assertTrue($model->isTranslatable([]));
    }

    public function test_is_translatable_handles_mixed_case_sensitivity()
    {
        $model = new TestTranslatableModel();

        // Assuming the trait is case-sensitive
        $this->assertFalse($model->isTranslatable('Title')); // Capital T
        $this->assertFalse($model->isTranslatable('TITLE')); // All caps
    }

    public function test_class_has_trait_function_works_correctly()
    {
        $model = new TestTranslatableModel();

        // Test that classHasTrait function works as expected
        $this->assertTrue(classHasTrait($model, 'Unusualify\Modularity\Entities\Traits\HasTranslation'));
        $this->assertTrue(classHasTrait($model, 'Unusualify\Modularity\Entities\Traits\IsTranslatable'));
        $this->assertFalse(classHasTrait($model, 'NonExistentTrait'));
    }

    public function test_property_exists_check_works_correctly()
    {
        $translatableModel = new TestTranslatableModel();
        $nonTranslatableModel = new TestTranslatableWithoutAttributesModel();

        $this->assertTrue(property_exists($translatableModel, 'translatedAttributes'));
        $this->assertFalse(property_exists($nonTranslatableModel, 'translatedAttributes'));
    }
}

// Test model that uses both HasTranslation and IsTranslatable traits
class TestTranslatableModel extends Model
{
    use HasTranslation, IsTranslatable;

    protected $translatedAttributes = ['title', 'description'];
}

// Test model that doesn't have HasTranslation trait
class TestNonTranslatableModel extends Model
{
    use IsTranslatable;

    protected $translatedAttributes = ['title', 'description'];
}

// Test model that has HasTranslation but no translatedAttributes property
class TestTranslatableWithoutAttributesModel extends Model
{
    use HasTranslation, IsTranslatable;
}

// Test model that has HasTranslation and empty translatedAttributes
class TestTranslatableEmptyAttributesModel extends Model
{
    use HasTranslation, IsTranslatable;

    protected $translatedAttributes = [];
}
