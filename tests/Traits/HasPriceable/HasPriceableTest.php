<?php

namespace Unusualify\Modularity\Tests\Traits\HasPriceable;

use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Request;
use Mockery;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\PriceType;
use Modules\SystemPricing\Entities\VatRate;


class HasPriceableTest extends TestCase
{
    use RefreshDatabase;

    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestTables();

        // Create test model table
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        $vatRates = [
            [
                'name' => 'TR-Standard VAT',
                // 'slug' => 'turkey-sta'
                'rate' => 20
            ],
            [
                'name' => 'TR-Reduced VAT',
                'rate' => 10
                // 'slug' => 'turkey-sta'
            ]
        ];

        foreach ($vatRates as $key => $vatRate) {
            VatRate::create($vatRate);
        }

        $currencies = [
            [
                'name' => 'Euro',
                'symbol' => '€',
                'iso_4217' => 'EUR',
                'iso_4217_number' => 978,
            ],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'iso_4217' => 'USD',
                'iso_4217_number' => 840,
            ],
            [
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'iso_4217' => 'TRY',
                'iso_4217_number' => 949
            ],
        ];

        foreach ($currencies as $key => $currency) {
            Currency::create($currency);
        }

        $prices = [

            [
                'currency_id' => 1,
                'display_price' => 2000,
            ],
            [
                'currency_id' => 2,
                'display_price' => 2000,
            ],
            [
                'currency_id' => 3,
                'display_price' => 2000,
            ],

        ];


        PriceType::create([
            'name' => 'Regular Price',
            'slug' => 'regular-price',
        ]);

        TestModel::create([
            'name' => 'Test model'
        ]);

        // Mock Request facade
        // $currencyObj = (object)[
        //     'id' => $this->currency,
        //     'iso_4217' => 'USD'
        // ];

        /*dd('Before recieve');
        $this->mock(Request::class, function ($mock) use ($currencyObj) {
            $mock->shouldReceive('getUserCurrency')
                ->andReturn($currencyObj);
        });


        // Mock PriceService facade
        $this->mock('Oobook\Priceable\Facades\PriceService', function ($mock) {
            $mock->shouldReceive('formatAmount')
                ->andReturn('$10.00');
        });

        */

        // // Create associated price
        // $this->price = DB::table('price_models')->insertGetId([
        //     'priceable_type' => get_class($this->testModel),
        //     'priceable_id' => $this->testModel->id,
        //     'price_type_id' => $this->priceType,
        //     'vat_rate_id' => $this->vatRate,
        //     'currency_id' => $this->currency,
        //     'display_price' => 1000,
        //     'price_excluding_vat' => 847,
        //     'price_including_vat' => 1000,
        //     'vat_amount' => 153,
        // ]);

    }

    protected function createTestTables (): void
    {
        // dd(
        //     Schema::getConnection()
        // );
        // Schema::create(config('priceable.tables.vat_rates'), function (Blueprint $table)
        // {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('slug')->unique();
        //     $table->unsignedFloat('rate');
        //     $table->timestamps();
        //     $table->softDeletes();

        // });
        // Schema::create(config('priceable.tables.currencies'), function (Blueprint $table)
        // {
        //     $table->id();
        //     $table->string('name');
        //     $table->text('symbol', 10)->nullable()->default(NULL);
        //     $table->string('iso_4217', 3)->default(null)->nullable();
        //     $table->integer('iso_4217_number')->default(null)->nullable();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });


        // Schema::create(config('priceable.tables.price_types'), function (Blueprint $table)
        // {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('slug')->unique();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        // Schema::create(config('priceable.tables.prices'), function (Blueprint $table)
        // {
        //     $table->id();
        //     $table->uuidMorphs('priceable');
        //     $table->unsignedBigInteger('price_type_id')->default(null)->nullable();
        //     $table->unsignedBigInteger('vat_rate_id');
        //     $table->unsignedBigInteger('currency_id');
        //     $table->bigInteger('display_price')->default(0);
        //     $table->bigInteger('price_excluding_vat')->default(0);
        //     $table->bigInteger('price_including_vat')->default(0);
        //     $table->bigInteger('vat_amount')->default(0);
        //     $table->timestamp('valid_from')->nullable()->default(null);
        //     $table->timestamp('valid_till')->nullable()->default(null);
        //     $table->timestamps();
        //     $table->softDeletes();

        //     $table->foreign('vat_rate_id')->references('id')->on('vat_rates');
        //     $table->foreign('currency_id')->references('id')->on('currencies');
        //     $table->foreign('price_type_id')->references('id')->on('price_types');
        // });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_have_prices()
    {

        $testModel = TestModel::find(1);
        $relation = $testModel->prices();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $relation);
        $this->assertEquals('priceable_type', $relation->getMorphType());
        $this->assertEquals(\Modules\SystemPricing\Entities\Price::class, get_class($relation->getRelated()));
    }

    /** @test */
    public function it_can_have_base_price()
    {

        $testModel = TestModel::find(1);
        $relation = $testModel->basePrice();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $relation);
        $this->assertEquals('priceable_type', $relation->getMorphType());
        $this->assertEquals(\Modules\SystemPricing\Entities\Price::class, get_class($relation->getRelated()));
    }


    /** @test */
    public function it_returns_correct_base_price_raw()
    {

        $testModel = TestModel::find(1);
        $testModel->prices()->create([
            'currency_id' => 1,
            'display_price' => 1000
        ]);

        $this->assertEquals(100000, $testModel->basePriceRaw);
    }

    /** @test */
    public function it_returns_correct_euro_base_price_formatted ()
    {
        $testModel = TestModel::find(1);

        $testModel->prices()->create(
            [
                'currency_id' => 1,
                'display_price' => 1000
            ],
        );


        $this->assertEquals("€\u{A0}1.000,00", $testModel->basePriceFormatted);

        //dd($testModel->basePriceFormatted);

    }

    /** @test */
    public function it_returns_correct_dollar_base_price_formatted ()
    {

        $testModel = TestModel::find(1);

        Request::setUserCurrency(Currency::find(2));

        $testModel->prices()->create(
            [
                'currency_id' => 1,
                'display_price' => 1000
            ],
        );
        $testModel->prices()->create(
            [
                'currency_id' => 2,
                'display_price' => 1000
            ]
        );

        $this->assertEquals("US$\u{A0}1.000,00", $testModel->basePriceFormatted);

    }

    /** @test */
    public function it_returns_correct_lira_base_price_formatted ()
    {

        $testModel = TestModel::find(1);

        Request::setUserCurrency(Currency::find(3));

        $testModel->prices()->create(
            [
                'currency_id' => 1,
                'display_price' => 1000
            ],
        );

        $testModel->prices()->create(
            [
                'currency_id' => 2,
                'display_price' => 1000
            ]
        );

        $testModel->prices()->create(
            [
                'currency_id' => 3,
                'display_price' => 1000
            ]
        );

        $this->assertEquals("TRY\u{A0}1.000,00", $testModel->basePriceFormatted);

    }

    /** @test */
    public function it_returns_correct_base_price_formatted_without_vat()
    {
        $testModel = TestModel::find(1);

        Request::setUserCurrency(Currency::find(1));

        $testModel->prices()->create(
            [
                'currency_id' => 1,
                'display_price' => 1000
            ],
        );

        $this->assertEquals("€\u{A0}1.000,00", $testModel->basePriceFormattedWithoutVat);


    }


}

    // $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);

    // $mockRequest->shouldReceive('getUserCurrency')->andReturn($mockedCurrency);
    // $mockRequest->shouldReceive('setUserResolver')->andReturnNull();

    // Swap the Request facade with the mocked request
    // Request::swap($mockRequest);

