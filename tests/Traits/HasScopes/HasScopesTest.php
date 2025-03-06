<?php

namespace Unusualify\Modularity\Tests\Traits\HasScopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Entities\Traits\HasScopes;
use Illuminate\Support\Carbon;

class HasScopesTest extends TestCase {

    use RefreshDatabase;

    protected function setup() : void
    {
        parent::setup();
        DB::statement('CREATE TABLE test_models (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            published BOOLEAN,
            public BOOLEAN,
            publish_start_date DATETIME,
            publish_end_date DATETIME,
            deleted_at DATETIME
            )'
        );

        $models= [
            [
                'published' => true,
                'public' => true,
                'deleted_at' => null,
            ],
            [
                'published' => false,
                'public' => true,
                'deleted_at' => now(),
            ],
            [
                'published' => true,
                'public' => false,
                'deleted_at' => null,
            ],

            [
                'publish_start_date' => Carbon::now()->subDay(),
                'publish_end_date' => Carbon::now()->addDay(),
            ],

            [
                'publish_start_date' => Carbon::now()->addDay(),
                'publish_end_date' => Carbon::now()->addDay(),
            ],
        ];

        foreach ($models as $model){
            TestModel::create($model);
        }

    }
    protected function tearDown(): void
    {
        DB::statement('DROP TABLE test_models');
        parent::tearDown();
    }

    //for published
    /** @test */
    public function scopePublished()
    {
        $published = TestModel::find(1);
        $allPublished= TestModel::published()->get();
        $this->assertCount(2, $allPublished);
        $this->assertTrue($allPublished->contains($published));
    }

    //for unpublished
    /** @test */
    public function scopeDraft()
    {

        $draft = TestModel::find(2);
        $allDrafts = TestModel::draft()->get();

        $this->assertCount(1, $allDrafts);
        $this->assertTrue($allDrafts->contains($draft));

    }

    /** @test */
    public function scopePublishedInListings()
    {
        $publishedInListing = TestModel::find(1);
        $notPublishedInListing = TestModel::find(2);

        $publishedInListings = TestModel::publishedInListings()->get();
        $this->assertCount(1, $publishedInListings);

        $this->assertTrue($publishedInListings->contains($publishedInListing));
        $this->assertFalse($publishedInListings->contains($notPublishedInListing));


    }


    /** @test */
    public function scopeVisible()
    {
        $visible = TestModel::find(5);
        $allVisibles = TestModel::visible()->get();
        $this->assertCount(4, $allVisibles);
        $this->assertTrue(!$allVisibles->contains($visible));
        //dd(sprintf("Right now in Istanbul is %s", Carbon::now('Europe/Istanbul')->toDateTimeString()));
    }

    /** @test */
    public function scopeOnlyTrashed()
    {

        $trashed = TestModel::find(2);
        $allTrashed = TestModel::onlyTrashed()->get();
        $this->assertCount(1, $allTrashed);
        $this->assertTrue($allTrashed->contains($trashed));
    }


}

class TestModel extends Model
{
    use HasScopes;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}

