<?php

namespace Modules\SystemPayment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\User\Entities\User;
use Unusualify\Modularity\Entities\Media;
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
                'name' => 'iyzico',
                'title' => 'Iyzico',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'iyzico.png',
                'currency' => 'TRY'
            ],
            [
                'name' => 'paypal',
                'title' => 'PayPal',
                'is_external' => true,
                'is_internal' => false,
                'image' => 'paypal.png',
                'currency' => 'USD'
            ],
            [
                'name' => 'garanti-pos',
                'title' => 'GarantiPOS',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'credit-card.png',
                'currency' => 'TRY'
            ],
            [
                'name' => 'teb-pos',
                'title' => 'TebPOS',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'credit-card.png',
                'currency' => 'TRY'
            ],
            [
                'name' => 'teb-common-pos',
                'title' => 'TebCommonPOS',
                'is_external' => false,
                'is_internal' => true,
                'image' => 'credit-card.png',
                'currency' => 'TRY'
            ],
            [
                'name' => 'ideal',
                'title' => 'iDEAL',
                'is_external' => true,
                'is_internal' => false,
                'image' => 'ideal.png',
                'currency' => 'EUR'
            ],
        ];

        $admin = User::where('email', 'admin@unusualgrowth.com')->first();
        if (! $admin) {
            $this->command->error('Admin user not found. Please ensure the admin user exists in the database.');
            return;
        }

        $superadmin = User::find(1);
        Auth::guard('unusual_users')->login($superadmin);

        foreach ($paymentServices as $service) {
            $paymentService = PaymentService::create([
                'name' => $service['name'],
                'title' => $service['title'],
                'is_external' => $service['is_external'],
                'is_internal' => $service['is_internal'],
            ]);

            $this->createAndAssociateImage($paymentService, $service['image']);

            // Get the specified currency for the payment service
            $currency = PaymentCurrency::where('iso_4217', $service['currency'])->first();
            if ($currency) {
                $paymentService->paymentCurrencies()->attach($currency->id);
            } else {
                $this->command->warn("Currency {$service['currency']} not found for {$service['name']}");
            }
        }

        Auth::logout();
    }

    /**
     * Create a media object for the image and associate it with the payment service.
     */
    private function createAndAssociateImage(PaymentService $paymentService, string $imageName)
    {
        $imagePath = public_path('vendor/modularity/assets/images/payment-service-images/' . $imageName);

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
