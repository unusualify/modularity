<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Translation;

use Illuminate\Filesystem\Filesystem;
use ReflectionMethod;
use Unusualify\Modularous\Support\FileLoader;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Translation\Translator;

class TranslatorTest extends TestCase
{
    private string $langPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->langPath = sys_get_temp_dir() . '/modularous_translator_' . uniqid();
        mkdir($this->langPath . '/en', 0777, true);
        file_put_contents($this->langPath . '/en/messages.php', "<?php return ['greeting' => 'Hello :name'];");
        file_put_contents($this->langPath . '/en/validation.php', "<?php return ['required' => 'Required'];");
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->langPath);

        parent::tearDown();
    }

    public function test_get_translations_loads_all_groups_for_configured_locales(): void
    {
        config()->set('translatable.locales', ['en']);

        $translator = $this->makeTranslator();

        $translations = $translator->getTranslations();

        $this->assertArrayHasKey('en', $translations);
        $this->assertSame('Hello :name', $translations['en']['messages']['greeting']);
        $this->assertSame('Required', $translations['en']['validation']['required']);
    }

    public function test_add_path_registers_an_additional_translation_directory(): void
    {
        $extraPath = $this->langPath . '/extra';
        mkdir($extraPath . '/en', 0777, true);
        file_put_contents($extraPath . '/en/custom.php', "<?php return ['title' => 'Custom'];");

        $translator = $this->makeTranslator();
        $translator->addPath($extraPath);

        $this->assertSame('Custom', $translator->get('custom.title', [], 'en'));
    }

    public function test_make_replacements_supports_colon_and_brace_placeholders(): void
    {
        $translator = $this->makeTranslator();
        $method = new ReflectionMethod($translator, 'makeReplacements');
        $method->setAccessible(true);

        $line = 'Hello :name, welcome {name}, shout :NAME';
        $result = $method->invoke($translator, $line, ['name' => 'Ada']);

        $this->assertSame('Hello Ada, welcome Ada, shout ADA', $result);
    }

    public function test_make_replacements_returns_line_when_replace_array_is_empty(): void
    {
        $translator = $this->makeTranslator();
        $method = new ReflectionMethod($translator, 'makeReplacements');
        $method->setAccessible(true);

        $this->assertSame('unchanged', $method->invoke($translator, 'unchanged', []));
    }

    private function makeTranslator(): Translator
    {
        $loader = new FileLoader(new Filesystem, $this->langPath);

        return new Translator($loader, 'en');
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                $this->removeDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($path);
    }
}
