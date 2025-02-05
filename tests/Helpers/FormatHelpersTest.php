<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Unusualify\Modularity\Tests\TestCase;
use stdClass;

class FormatHelpersTest extends TestCase
{
    /** @test */
    public function test_lower_name()
    {
        $this->assertEquals('hello_world', lowerName('HelloWorld'));
        $this->assertEquals('hello_world', lowerName('helloWorld'));
        $this->assertEquals('hello_world', lowerName('hello_world'));
    }

    /** @test */
    public function test_studly_name()
    {
        $this->assertEquals('HelloWorld', studlyName('hello_world'));
        $this->assertEquals('HelloWorld', studlyName('helloWorld'));
        $this->assertEquals('HelloWorld', studlyName('hello-world'));
    }

    /** @test */
    public function test_camel_case()
    {
        $this->assertEquals('helloWorld', camelCase('hello_world'));
        $this->assertEquals('helloWorld', camelCase('HelloWorld'));
        $this->assertEquals('helloWorld', camelCase('hello-world'));
    }

    /** @test */
    public function test_kebab_case()
    {
        $this->assertEquals('hello-world', kebabCase('HelloWorld'));
        $this->assertEquals('hello-world', kebabCase('helloWorld'));
    }

    /** @test */
    public function test_snake_case()
    {
        $this->assertEquals('hello_world', snakeCase('HelloWorld'));
        $this->assertEquals('hello_world', snakeCase('helloWorld'));
    }

    /** @test */
    public function test_pluralize()
    {
        $this->assertEquals('users', pluralize('user'));
        $this->assertEquals('categories', pluralize('category'));
        $this->assertEquals('children', pluralize('child'));
    }

    /** @test */
    public function test_singularize()
    {
        $this->assertEquals('user', singularize('users'));
        $this->assertEquals('category', singularize('categories'));
        $this->assertEquals('child', singularize('children'));
    }

    /** @test */
    public function test_headline()
    {
        $this->assertEquals('Hello World', headline('hello_world'));
        $this->assertEquals('Hello World', headline('helloWorld'));
        $this->assertEquals('Hello World', headline('hello-world'));
    }

    /** @test */
    public function test_table_name()
    {
        $this->assertEquals('users', tableName('User'));
        $this->assertEquals('blog_posts', tableName('BlogPost'));
        $this->assertEquals('product_categories', tableName('ProductCategory'));
    }

    /** @test */
    public function test_make_foreign_key()
    {
        $this->assertEquals('user_id', makeForeignKey('User'));
        $this->assertEquals('blog_post_id', makeForeignKey('BlogPost'));
        $this->assertEquals('category_id', makeForeignKey('Category'));
    }

    /** @test */
    public function test_make_morph_name()
    {
        $this->assertEquals('userable', makeMorphName('User'));
        $this->assertEquals('postable', makeMorphName('Post'));
        $this->assertEquals('imageable', makeMorphName('Image'));
    }

    /** @test */
    public function test_make_morph_foreign_key()
    {
        $this->assertEquals('userable_id', makeMorphForeignKey('User'));
        $this->assertEquals('postable_id', makeMorphForeignKey('Post'));
        $this->assertEquals('imageable_id', makeMorphForeignKey('Image'));
    }

    /** @test */
    public function test_make_morph_foreign_type()
    {
        $this->assertEquals('userable_type', makeMorphForeignType('User'));
        $this->assertEquals('postable_type', makeMorphForeignType('Post'));
        $this->assertEquals('imageable_type', makeMorphForeignType('Image'));
    }

    /** @test */
    public function test_get_morph_model_name()
    {
        $this->assertEquals('User', getMorphModelName('userable'));
        $this->assertEquals('Post', getMorphModelName('postable'));
        $this->assertEquals('Image', getMorphModelName('imageable'));
    }

    /** @test */
    public function test_abbreviation()
    {
        $this->assertEquals('HW', abbreviation('hello_world'));
        $this->assertEquals('BP', abbreviation('blog_post'));
        $this->assertEquals('PC', abbreviation('product_category'));
    }

    /** @test */
    public function test_get_class_short_name()
    {
        $this->assertEquals('stdClass', get_class_short_name(stdClass::class));
        $this->assertEquals('TestCase', get_class_short_name(TestCase::class));
        $this->assertEquals('FormatHelpersTest', get_class_short_name(self::class));
    }

    /** @test */
    public function test_class_resolution()
    {
        $this->assertEquals('\stdClass::class', class_resolution('stdClass'));
        $this->assertEquals('\App\Models\User::class', class_resolution('App\Models\User'));
    }

    /** @test */
    public function test_camel_case_to_words()
    {
        $this->assertEquals('Hello world', camelCaseToWords('helloWorld'));
        $this->assertEquals('Blog post title', camelCaseToWords('blogPostTitle'));
        $this->assertEquals('Product category name', camelCaseToWords('productCategoryName'));
    }

    /** @test */
    public function test_get_value_or_null()
    {
        // Test with simple values
        $this->assertNull(getValueOrNull(''));
        $this->assertNull(getValueOrNull([]));
        $this->assertEquals('value', getValueOrNull('value'));

        // Test with arrays
        $array = ['key' => 'value', 'empty' => ''];
        $this->assertEquals('value', getValueOrNull($array, 'key'));
        $this->assertNull(getValueOrNull($array, 'empty'));
        $this->assertNull(getValueOrNull($array, 'nonexistent'));

        // Test with boolean return
        $this->assertFalse(getValueOrNull('', null, true));
        $this->assertFalse(getValueOrNull([], null, true));
    }

    /** @test */
    public function test_wrap_implode()
    {
        $array = ['one', 'two', 'three'];

        $this->assertEquals('[one,two,three]', wrapImplode(',', $array, '[', ']'));
        $this->assertEquals('(one|two|three)', wrapImplode('|', $array, '(', ')'));
        $this->assertEquals('', wrapImplode(',', [], '[', ']'));
    }

    /** @test */
    public function test_nested_route_name_format()
    {
        $this->assertEquals('users.nested.posts', nestedRouteNameFormat('users', 'posts'));
        $this->assertEquals('blog_posts.nested.comments', nestedRouteNameFormat('blog_posts', 'comments'));
    }

    /** @test */
    public function test_replace_curly_braces()
    {
        // Test with array replacements
        $replacements = ['name' => 'John', 'age' => '30'];
        $this->assertEquals(
            'Hello John, you are 30 years old',
            replace_curly_braces('Hello {name}, you are {age} years old', $replacements)
        );

        // Test with object replacements
        $obj = new stdClass();
        $obj->name = 'John';
        $obj->age = '30';
        $this->assertEquals(
            'Hello John, you are 30 years old',
            replace_curly_braces('Hello {name}, you are {age} years old', $obj)
        );

        // Test with indexed array
        $indexed = ['John', '30'];
        $this->assertEquals(
            'Hello John, you are 30 years old',
            replace_curly_braces('Hello {}, you are {} years old', $indexed)
        );
    }

    /** @test */
    public function test_concatenate_path()
    {
        $this->assertEquals(
            'path/to/file',
            concatenate_path('path/to', 'file')
        );
        $this->assertEquals(
            'path/to/file',
            concatenate_path('path/to/', '/file')
        );
    }

    /** @test */
    public function test_concatenate_namespace()
    {
        $this->assertEquals(
            'App\Models\User',
            concatenate_namespace('App\Models', 'User')
        );
        $this->assertEquals(
            'App\Models\User',
            concatenate_namespace('App\Models\\', '\\User')
        );
    }
}
