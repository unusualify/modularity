<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ModuleRouteInspect;

use Unusualify\Modularous\Entities\Traits\HasRevisions;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;
use Unusualify\Modularous\Services\ModuleRouteInspect\FeatureDetector;
use Unusualify\Modularous\Tests\TestCase;

class FeatureDetectorTest extends TestCase
{
    /** @test */
    public function it_maps_option_keys_to_feature_keys(): void
    {
        $detector = new FeatureDetector;

        $this->assertSame('translation', $detector->optionKeyToFeatureKey('addTranslation'));
        $this->assertSame('cmr', $detector->optionKeyToFeatureKey('addCmr'));
        $this->assertSame('parent_segment', $detector->optionKeyToFeatureKey('addParentSegment'));
        $this->assertSame('revisions', $detector->optionKeyToFeatureKey('addRevisions'));
    }

    /** @test */
    public function it_builds_definitions_from_traits_config(): void
    {
        $definitions = (new FeatureDetector)->definitions();

        $this->assertArrayHasKey('translation', $definitions);
        $this->assertSame(HasTranslation::class, $definitions['translation']['model']);
        $this->assertSame(TranslationsTrait::class, $definitions['translation']['repository']);

        $this->assertArrayHasKey('revisions', $definitions);
        $this->assertSame(HasRevisions::class, $definitions['revisions']['model']);
        $this->assertSame(RevisionsTrait::class, $definitions['revisions']['repository']);

        $this->assertArrayHasKey('singular', $definitions);
        $this->assertNull($definitions['singular']['repository']);
    }

    /** @test */
    public function it_detects_present_features_and_companion_mismatches(): void
    {
        $model = new class
        {
            use HasRevisions;
        };

        $repository = new class
        {
            // Intentionally missing RevisionsTrait
        };

        $result = (new FeatureDetector)->detect($model::class, $repository::class);

        $this->assertTrue($result['features']['revisions']['present']);
        $this->assertTrue($result['features']['revisions']['model']);
        $this->assertFalse($result['features']['revisions']['repository']);

        $codes = array_map(static fn ($finding) => $finding->code, $result['findings']);
        $this->assertContains('missing_repository_companion', $codes);

        $companion = collect($result['findings'])->firstWhere('code', 'missing_repository_companion');
        $this->assertNotNull($companion);
        $this->assertSame('revisions', $companion->feature);
    }

    /** @test */
    public function it_detects_aligned_translation_companions(): void
    {
        $model = new class
        {
            use HasTranslation;
        };

        $repository = new class
        {
            use TranslationsTrait;
        };

        $result = (new FeatureDetector)->detect($model::class, $repository::class);

        $this->assertTrue($result['features']['translation']['present']);
        $this->assertTrue($result['features']['translation']['model']);
        $this->assertTrue($result['features']['translation']['repository']);

        $translationFindings = array_filter(
            $result['findings'],
            static fn ($finding) => str_contains($finding->message, 'translation')
        );
        $this->assertEmpty($translationFindings);
    }
}
