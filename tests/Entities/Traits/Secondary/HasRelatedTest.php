<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities\Traits\Secondary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\RelatedItem;
use Unusualify\Modularous\Entities\Traits\Secondary\HasRelated;
use Unusualify\Modularous\Tests\TestCase;

class HasRelatedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('has_related_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
        });

        Schema::create('um_related_items', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('related');
            $table->string('browser_name')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('um_related_items');
        Schema::dropIfExists('has_related_subjects');
        parent::tearDown();
    }

    /** @test */
    public function it_saves_loads_and_clears_related_browser_items(): void
    {
        config(['modularous.related_table' => 'um_related_items']);

        $subject = HasRelatedSubject::query()->create(['name' => 'Subject']);
        $related = HasRelatedSubject::query()->create(['name' => 'Related']);

        $subject->saveRelated([
            ['id' => $related->id, 'endpointType' => HasRelatedSubject::class],
        ], 'pages');

        // Fresh instance forces load('relatedItems') path in loadRelated().
        $fresh = HasRelatedSubject::query()->findOrFail($subject->id);
        $loaded = $fresh->getRelated('pages');
        $this->assertCount(1, $loaded);
        $this->assertSame($related->id, $loaded->first()->id);

        // Second call should hit the in-memory related cache path.
        $cached = $fresh->getRelated('pages');
        $this->assertCount(1, $cached);
        $this->assertSame($related->id, $cached->first()->id);

        $subject->clearRelated('pages');
        $this->assertCount(0, $subject->fresh()->relatedItems);

        $subject->saveRelated([
            ['id' => $related->id, 'endpointType' => HasRelatedSubject::class],
        ], 'pages');
        $subject->clearAllRelated();
        $this->assertCount(0, RelatedItem::query()->where('subject_id', $subject->id)->get());
    }
}

class HasRelatedSubject extends Model
{
    use HasRelated;

    protected $table = 'has_related_subjects';

    protected $guarded = [];

    public $timestamps = false;
}
