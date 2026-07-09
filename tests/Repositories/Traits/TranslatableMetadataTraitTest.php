<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait;
use Unusualify\Modularous\Tests\TestCase;

class TranslatableMetadataTraitTest extends TestCase
{
    public function test_appends_default_form_inputs_when_model_supports_metadata(): void
    {
        $repository = new class
        {
            use TranslatableMetadataTrait;

            public function hasModelTrait(string $trait): bool
            {
                return $trait === HasTranslatableMetadata::class;
            }
        };

        $inputs = $repository->appendFormSchemaTranslatableMetadataTrait();

        $this->assertNotEmpty($inputs);
        $this->assertArrayHasKey('name', $inputs[0]);
    }

    public function test_returns_empty_schema_when_model_does_not_support_metadata(): void
    {
        $repository = new class
        {
            use TranslatableMetadataTrait;

            public function hasModelTrait(string $trait): bool
            {
                return false;
            }
        };

        $this->assertSame([], $repository->appendFormSchemaTranslatableMetadataTrait());
    }
}
