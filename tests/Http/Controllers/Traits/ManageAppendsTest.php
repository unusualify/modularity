<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Unusualify\Modularous\Http\Controllers\Traits\ManageAppends;
use Unusualify\Modularous\Tests\TestCase;

class ManageAppendsTest extends TestCase
{
    /** @test */
    public function it_merges_index_and_form_appends_including_modal_overlap(): void
    {
        $controller = new class
        {
            use ManageAppends;

            public array $tableAttributes = ['editOnModal' => true];

            public function addIndexAppendsExtra(): array
            {
                return ['status'];
            }

            public function addFormAppendsExtra(): array
            {
                return ['author as author_name'];
            }
        };

        $controller->addIndexAppends();
        $controller->addFormAppends();

        $this->assertContains('status', $controller->getIndexAppends());
        $this->assertContains('author as author_name', $controller->getFormAppends());
        $this->assertContains('author as author_name', $controller->getIndexAppends());

        $controller->tableAttributes['editOnModal'] = false;
        $this->assertSame(['status', 'author as author_name'], array_values($controller->getIndexAppends()));
    }
}
