<?php

namespace Unusualify\Modularous\Tests\Support;

use Unusualify\Modularous\Support\PublishableMetadata;
use Unusualify\Modularous\Tests\TestCase;

class PublishableMetadataTest extends TestCase
{
    public function test_default_form_inputs_without_translated_fields(): void
    {
        $inputs = PublishableMetadata::defaultFormInputs();

        $this->assertSame([
            'published' => false,
            'publish_start_date' => false,
            'publish_end_date' => false,
        ], $this->translatedFlagsByName($inputs));
    }

    public function test_default_form_inputs_with_legacy_all_translated_flag(): void
    {
        $inputs = PublishableMetadata::defaultFormInputs(true);

        $this->assertSame([
            'published' => true,
            'publish_start_date' => true,
            'publish_end_date' => true,
        ], $this->translatedFlagsByName($inputs));
    }

    public function test_default_form_inputs_with_per_field_translated_flags(): void
    {
        $inputs = PublishableMetadata::defaultFormInputs(['published', 'publish_end_date']);

        $this->assertSame([
            'published' => true,
            'publish_start_date' => false,
            'publish_end_date' => true,
        ], $this->translatedFlagsByName($inputs));
    }

    public function test_normalize_translated_fields_filters_unknown_keys(): void
    {
        $this->assertSame(
            ['published'],
            PublishableMetadata::normalizeTranslatedFields(['published', 'title', 'seo_title'])
        );
    }

    /**
     * @param list<array<string, mixed>> $inputs
     * @return array<string, bool>
     */
    private function translatedFlagsByName(array $inputs): array
    {
        $flags = [];

        foreach ($inputs as $input) {
            $flags[$input['name']] = (bool) ($input['translated'] ?? false);
        }

        return $flags;
    }
}
