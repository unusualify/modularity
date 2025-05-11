<?php

namespace Modules\SystemPayment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\MediaLibraryController;
use Unusualify\Modularity\Http\Requests\MediaRequest;

class PaymentServiceSeeder extends Seeder
{
    protected $mediaLibraryController;

    public function __construct(MediaLibraryController $mediaLibraryController)
    {
        $this->mediaLibraryController = $mediaLibraryController;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentServices = [
            [
                'name' => 'Iyzico',
                'key' => 'iyzico',
                'is_external' => false,
                'is_internal' => false,
                'image' => 'iyzico.png',
            ],
            [
                'name' => 'Paypal',
                'key' => 'paypal',
                'is_external' => true,
                'is_internal' => false,
                'image' => 'paypal.png',
                'published' => true,
                'button_style' => 'background-color: #FCBB32 !important; color: #002C6F !important;',

                'paymentCurrencies' => [
                    PaymentCurrency::where('iso_4217', 'US')->first()->id ?? null,
                    PaymentCurrency::where('iso_4217', 'EUR')->first()->id ?? null,
                ]
            ],
            [
                'name' => 'GarantiPOS',
                'key' => 'garanti-pos',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'credit-card.png',
                'published' => true,
                'paymentCurrencies' => [
                    PaymentCurrency::where('iso_4217', 'EUR')->first()->id ?? null,
                ],
                'internalPaymentCurrencies' => [
                    'TRY',
                    'USD',
                    'EUR',
                ]
            ],
            [
                'name' => 'TebPOS',
                'key' => 'teb-pos',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'credit-card.png',
            ],
            [
                'name' => 'TebCommonPOS',
                'key' => 'teb-common-pos',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'credit-card.png',
                // 'currency' => 'TRY',
            ],
            [
                'name' => 'iDEAL',
                'key' => 'ideal',
                'is_external' => true,
                'is_internal' => false,
                'image' => 'ideal.png',
                'published' => true,
                'button_style' => 'background-color: rgb(var(--v-theme-grey-lighten-2)) !important;',

                'paymentCurrencies' => [
                    PaymentCurrency::where('iso_4217', 'EUR')->first()->id ?? null,
                ]
            ],
            [
                'name' => 'iDEAL QR',
                'key' => 'ideal-qr',
                'is_external' => true,
                'is_internal' => false,
                'image' => 'ideal-qr.png',
                'published' => true,
                'button_style' => 'background-color: rgb(var(--v-theme-grey-lighten-2)) !important;',

                'paymentCurrencies' => [
                    PaymentCurrency::where('iso_4217', 'EUR')->first()->id ?? null,
                ],
            ],
        ];

        $superadmin = User::role('superadmin', Modularity::getAuthGuardName())->first();

        if (! $superadmin) {
            $this->command->error('Admin user not found. Please ensure the admin user exists in the database.');

            return;
        }

        Auth::guard(Modularity::getAuthGuardName())->login($superadmin);

        foreach ($paymentServices as $_paymentService) {
            $paymentService = PaymentService::create(Arr::only($_paymentService, ['name', 'key', 'is_external', 'is_internal']));

            $this->createAndAssociateImage($paymentService, $_paymentService['image']);

            // Get the specified currency for the payment service
            if (isset($_paymentService['paymentCurrencies'])) {
                foreach($_paymentService['paymentCurrencies'] as $currency_id) {
                    $paymentCurrency = PaymentCurrency::find($currency_id);
                    if($paymentCurrency) {
                        $paymentService->paymentCurrencies()->attach($paymentCurrency->id);
                    }
                }
            }

            foreach($_paymentService['internalPaymentCurrencies'] ?? [] as $iso_4217) {
                $paymentCurrency = PaymentCurrency::firstWhere('iso_4217', $iso_4217);

                if(!$paymentCurrency->payment_service_id) {
                    $paymentCurrency->update([
                        'payment_service_id' => $paymentService->id,
                    ]);
                }
            }
        }

        Auth::logout();
    }

    /**
     * Create a media object for the image and associate it with the payment service.
     */
    private function createAndAssociateImage(PaymentService $paymentService, string $imageName, ?string $base_path = null)
    {
        if ($base_path) {
            $imagePath = $base_path . $imageName;

        } else {
            $imagePath = base_path('vendor/unusualify/modularity/resources/assets/images/payment-services/' . $imageName);
        }
        $imagePath = base_path('vendor/unusualify/modularity/resources/assets/images/payment-services/' . $imageName);
        // $this->command->warn("Image path: $imagePath");

        if (file_exists($imagePath)) {
            $file = new UploadedFile($imagePath, $imageName, null, null, true);

            $request = new MediaRequest;
            $request->files->set('qqfile', $file);
            $request->merge([
                'qqfilename' => $imageName,
                'unique_folder_name' => Str::uuid()->toString(),
            ]);

            $media = $this->mediaLibraryController->storeFile($request);

            if ($media) {
                // dd('here');
                $paymentService->medias()->attach($media->id, [
                    'role' => 'logo',
                    'crop' => 'default',
                    'locale' => 'en',
                    'metadatas' => json_encode([
                        'alt_text' => $paymentService->title . ' logo',
                        'caption' => $paymentService->title . ' payment service logo',
                    ]),
                ]);
            } else {
                $this->command->warn("Failed to create media for: $imageName");
            }
        } else {
            $this->command->warn("Image file not found: $imageName");
        }
    }
}
