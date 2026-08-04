<?php

namespace Unusualify\Modularous\Tests\Helpers;

use stdClass;
use Unusualify\Modularous\Tests\TestCase;

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

        $this->assertEquals('accessible', makeMorphName('Access', 'ible'));
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
        $obj = new stdClass;
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

    /** @test */
    public function test_class_namespace()
    {
        $this->assertSame(__NAMESPACE__, class_namespace(self::class));
    }

    /** @test */
    public function test_parse_and_format_rules_schema()
    {
        $parsed = parseRulesSchema([
            'email' => 'required|email:rfc|max:255',
            'name' => 'nullable|string',
        ]);

        $this->assertSame(['required' => '', 'email' => 'rfc', 'max' => '255'], $parsed['email']);
        $this->assertSame('required|email:rfc|max:255', formatRulesSchema($parsed)['email']);
    }

    /** @test */
    public function test_try_operation_returns_fallback_on_exception()
    {
        $this->assertSame('fallback', tryOperation(fn () => throw new \RuntimeException('boom'), 'fallback'));
        $this->assertSame(10, tryOperation(fn () => 10, 'fallback'));
    }

    /** @test */
    public function test_indent_and_comment_string()
    {
        $this->assertSame('    hello', indent(4, 'hello'));

        $asArray = comment_string('Does a thing', ['string $name', 'int $count=1'], 'void', null, true);
        $this->assertSame('/**', trim($asArray[0]));
        $this->assertStringContainsString('@param string $name', implode("\n", $asArray));
        $this->assertStringContainsString('@return void', implode("\n", $asArray));

        $asString = comment_string(['Line one', 'Line two'], [], null, 'string');
        $this->assertStringContainsString('@var string', $asString);
    }

    /** @test */
    public function test_method_string_and_attribute_string()
    {
        $method = method_string('demo', 'return true;', 'public', 'Demo method', ['string $foo'], 'bool');
        $this->assertStringContainsString('function demo(string $foo): bool', $method);
        $this->assertStringContainsString('return true;', $method);

        $attribute = attribute_string('demo', 'x', 'public', 'Demo attr');
        $this->assertStringContainsString("\$demo = 'x';", $attribute);

        $attributeArray = attribute_string('items', ['a' => 1], 'public', 'Items');
        $this->assertStringContainsString('$items =', $attributeArray);
    }

    /** @test */
    public function test_replace_variables_from_haystack()
    {
        $this->assertSame('Hello world', replace_variables_from_haystack('Hello ${name}$', ['name' => 'world']));
        $this->assertSame('fallback', replace_variables_from_haystack('${missing??fallback}$', []));
        $this->assertSame(5, replace_variables_from_haystack(5, []));

        $nested = replace_variables_from_haystack([
            'title' => 'Hi ${name}$',
            'meta' => ['label' => '${label}$'],
        ], ['name' => 'Ada', 'label' => 'L']);

        $this->assertSame('Hi Ada', $nested['title']);
        $this->assertSame('L', $nested['meta']['label']);
    }

    /** @test */
    public function test_extract_schema_extensions()
    {
        $this->assertSame([], extract_schema_extensions('not-array'));

        $results = extract_schema_extensions([
            'name' => 'country',
            'parentName' => 'package',
            'itemTitle' => 'name',
            'itemValue' => 'id',
            'regions' => [['id' => 1]],
            'ext' => [
                ['set', 'target.path', 'items', 'regions.*.id'],
                ['prependSchema', 'ignored', 'x', 'y'],
            ],
        ]);

        $this->assertCount(1, $results);
        $this->assertSame('set', $results[0]['format']);
        $this->assertSame('regions', $results[0]['setterKey']);
        $this->assertSame('package.country', $results[0]['modelNotation']);
    }

    /** @test */
    public function test_data_get_and_set_with_dot_keys()
    {
        $this->assertSame('default', data_get_with_dot_keys(null, 'a.b', 'default'));
        $this->assertSame('v', data_get_with_dot_keys(['a' => ['b' => 'v']], 'a.b'));
        $this->assertSame('obj', data_get_with_dot_keys((object) ['a' => (object) ['b' => 'obj']], 'a.b'));
        $this->assertSame('default', data_get_with_dot_keys(['a' => 1], 'a.b', 'default'));

        $target = [];
        data_set_with_dot_keys($target, 'a.b.c', 9);
        $this->assertSame(9, $target['a']['b']['c']);

        $object = (object) [];
        data_set_with_dot_keys($object, 'x.y', 'z');
        $this->assertSame('z', data_get($object, 'x.y'));
        $this->assertSame('z', is_array($object->x) ? $object->x['y'] : $object->x->y);
    }

    /** @test */
    public function test_name_surname_resolver_and_closure_transforms()
    {
        $this->assertSame(['Ada', 'Lovelace'], name_surname_resolver(' Ada Lovelace '));
        $this->assertSame(3, transform_closure_value(fn () => 3));
        $this->assertSame('plain', transform_closure_value('plain'));

        $values = transform_closure_values([
            'a' => fn () => 1,
            'b' => 'keep',
            'c' => ['nested' => fn () => 2],
        ]);
        $this->assertSame(1, $values['a']);
        $this->assertSame('keep', $values['b']);
        $this->assertSame(2, $values['c']['nested']);
    }

    /** @test */
    public function test_model_show_format_is_plural_and_get_file_class(): void
    {
        $model = new class
        {
            public string $name = 'Widget';

            public function getShowFormat(): string
            {
                return 'formatted-widget';
            }
        };
        $ref = $model;
        $this->assertSame('formatted-widget', modelShowFormat($ref));

        $plain = new class
        {
            public string $name = 'Plain';
        };
        $plainRef = $plain;
        $this->assertSame('Plain', modelShowFormat($plainRef));

        $this->assertTrue(is_plural('users'));
        $this->assertFalse(is_plural('user'));

        $tmp = tempnam(sys_get_temp_dir(), 'modclass');
        file_put_contents($tmp, "<?php\nnamespace Foo\\Bar;\nclass Demo {}\n");
        $this->assertSame('Foo\\Bar\\Demo', get_file_class($tmp));
        @unlink($tmp);
    }

    /** @test */
    public function test_laravel_relationship_map_and_file_trace(): void
    {
        $map = laravelRelationshipMap();
        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        $path = fileTrace('/FormatHelpersTest\.php/');
        $this->assertStringContainsString('FormatHelpersTest.php', $path);
    }

    /** @test */
    public function test_extract_schema_extensions_nested_children(): void
    {
        $results = extract_schema_extensions([
            'wrapper' => [
                'schema' => [
                    [
                        'name' => 'child',
                        'options' => [['id' => 1]],
                        'ext' => [
                            ['set', 'target', 'items', 'options.*.id'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertNotEmpty($results);
        $this->assertSame('set', $results[0]['format']);
    }
}
