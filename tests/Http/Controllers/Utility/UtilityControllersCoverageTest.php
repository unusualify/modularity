<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Utility;

use Illuminate\Http\Request;
use Mockery;
use Unusualify\Modularous\Http\Controllers\Utility\CurrencyExchangeController;
use Unusualify\Modularous\Http\Controllers\Utility\FilepondController;
use Unusualify\Modularous\Services\CurrencyExchangeService;
use Unusualify\Modularous\Services\FilepondManager;
use Unusualify\Modularous\Tests\TestCase;

class UtilityControllersCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function filepond_controller_delegates_to_manager(): void
    {
        $manager = Mockery::mock(FilepondManager::class);
        $manager->shouldReceive('createTemporaryFilepond')->once()->andReturn(response('uploaded'));
        $manager->shouldReceive('deleteTemporaryFilepond')->once()->andReturn(response('reverted'));
        $manager->shouldReceive('previewFile')->once()->with('folder-1')->andReturn(response('preview'));

        $controller = new FilepondController($manager);
        $request = Request::create('/filepond');

        $this->assertSame('uploaded', $controller->upload($request)->getContent());
        $this->assertSame('reverted', $controller->revert($request)->getContent());
        $this->assertSame('preview', $controller->preview($request, 'folder-1')->getContent());
    }

    /** @test */
    public function currency_exchange_controller_handles_success_and_failure_paths(): void
    {
        $service = Mockery::mock(CurrencyExchangeService::class);
        $service->shouldReceive('fetchExchangeRates')->once();
        $service->shouldReceive('convertTo')->once()->andReturn(100);
        $service->shouldReceive('getExchangeRate')->twice()->andReturn(2.5);

        $controller = new CurrencyExchangeController($service);

        $this->assertSame(200, $controller->fetchRates()->getStatusCode());

        $convert = $controller->convert(Request::create('/convert', 'POST', [
            'amount' => 50,
            'currency' => 'usd',
        ]));
        $this->assertSame(100, $convert->getData(true)['converted_amount']);

        $rate = $controller->getRate(Request::create('/rate'), 'eur');
        $this->assertSame(2.5, $rate->getData(true)['rate']);

        $failing = Mockery::mock(CurrencyExchangeService::class);
        $failing->shouldReceive('fetchExchangeRates')->once()->andThrow(new \RuntimeException('fail'));
        $failing->shouldReceive('convertTo')->once()->andThrow(new \RuntimeException('fail'));
        $failing->shouldReceive('getExchangeRate')->once()->andThrow(new \RuntimeException('fail'));

        $controllerFail = new CurrencyExchangeController($failing);
        $this->assertSame(500, $controllerFail->fetchRates()->getStatusCode());
        $this->assertSame(400, $controllerFail->convert(Request::create('/convert', 'POST', [
            'amount' => 1,
            'currency' => 'usd',
        ]))->getStatusCode());
        $this->assertSame(404, $controllerFail->getRate(Request::create('/rate'), 'xxx')->getStatusCode());
    }
}
