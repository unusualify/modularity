<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities;

use Unusualify\Modularous\Entities\Block;
use Unusualify\Modularous\Tests\TestCase;

class BlockTest extends TestCase
{
    /** @test */
    public function it_reads_content_helpers_and_table_presenter(): void
    {
        config(['modularous.blocks_table' => 'custom_blocks']);
        config(['modularous.block_editor.block_presenter_path' => null]);

        $block = new Block;
        $block->forceFill([
            'content' => [
                'title' => 'Hello',
                'label' => ['en' => 'English', 'tr' => 'Turkish'],
                'browsers' => ['pages' => [1, 2]],
                'enabled' => [true],
            ],
            'editor_name' => 'default',
        ]);
        $block->syncOriginal();

        $this->assertSame('Hello', $block->input('title'));
        $this->assertNull($block->input('missing'));
        $this->assertSame('English', $block->translatedInput('label', 'en'));
        config([
            'translatable.use_property_fallback' => true,
            'translatable.fallback_locale' => 'en',
            'app.locale' => 'de',
        ]);
        $this->assertSame('English', $block->translatedInput('label'));
        $this->assertSame([1, 2], $block->browserIds('pages'));
        $this->assertSame([], $block->browserIds('missing'));
        $this->assertTrue((bool) $block->checkbox('enabled'));
        $this->assertFalse((bool) $block->checkbox('missing'));
        $this->assertSame('custom_blocks', $block->getTable());
        $this->assertNull($block->getPresenterAttribute());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $block->blockable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $block->children());

        config(['modularous.block_editor.block_presenter_path' => 'App\\Presenters\\BlockPresenter']);
        $this->assertSame('App\\Presenters\\BlockPresenter', $block->getPresenterAttribute());
    }

    /** @test */
    public function editor_scope_builds_expected_constraints(): void
    {
        $defaultSql = Block::query()->editor('default')->toSql();
        $customSql = Block::query()->editor('custom')->toSql();

        $this->assertStringContainsString('editor_name', $defaultSql);
        $this->assertStringContainsString('editor_name', $customSql);
    }
}
