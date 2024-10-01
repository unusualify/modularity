<?php

namespace Modules\SystemPayment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPayment\Entities\PaymentCurrency;

class PaymentServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentServices = [
            [
                'name' => 'iyzico',
                'title' => 'Iyzico',
                'is_external' => false,
                'is_internal' => true,
            ],
            [
                'name' => 'paypal',
                'title' => 'PayPal',
                'is_external' => true,
                'is_internal' => false,
            ],
            [
                'name' => 'garanti-pos',
                'title' => 'GarantiPOS',
                'is_external' => false,
                'is_internal' => true,
            ],
            [
                'name' => 'teb-pos',
                'title' => 'TebPOS',
                'is_external' => false,
                'is_internal' => true,
            ],
            [
                'name' => 'teb-common-pos',
                'title' => 'TebCommonPOS',
                'is_external' => false,
                'is_internal' => true,
            ],
            [
                'name' => 'ideal',
                'title' => 'iDEAL',
                'is_external' => true,
                'is_internal' => false,
            ],
        ];

        foreach ($paymentServices as $service) {
            $paymentService = PaymentService::create($service);

            // Attach some random currencies to each payment service
            $currencies = PaymentCurrency::inRandomOrder()->take(rand(1, 3))->get();
            $paymentService->paymentCurrencies()->attach($currencies);
        }
    }
}
