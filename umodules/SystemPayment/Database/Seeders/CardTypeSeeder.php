<?php

namespace Modules\SystemPayment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\SystemPayment\Entities\CardType;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Http\Controllers\MediaLibraryController;
use Unusualify\Modularity\Http\Requests\MediaRequest;

class CardTypeSeeder extends Seeder
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
        $cardTypes = [
            [
                'name' => 'MasterCard',
                'card_type' => 'mastercard',
                'image' => 'mastercard.png',
                'paymentServices' => [1,3,4,5],
            ],
            [
                'name' => 'Visa',
                'card_type' => 'Visa',
                'image' => 'visa.png',
                'paymentServices' => [1,3,4,5],
            ],
            [
                'name' => 'Amex',
                'card_type' => 'amex',
                'image' => 'amex.png',
                'paymentServices' => [1,3,4,5], // Creation order of payment services in seeder
            ]
        ];

        $superadmin = User::role('superadmin', 'unusual_users')->first();

        if (! $superadmin) {
            $this->command->error('Admin user not found. Please ensure the admin user exists in the database.');

            return;
        }

        Auth::guard('unusual_users')->login($superadmin);

        foreach ($cardTypes as $types) {
            $cardType = CardType::create([
                'name' => $types['name'],
                'card_type' => $types['card_type'],
            ]);
            // dd($cardType);
            // dd($cardType);
            $this->createAndAssociateImage($cardType, $types['image']);

            foreach($cardType['paymentServices'] as $paymentService){
                $cardType->paymentServices()->attach($paymentService);
            }

            // Get the specified currency for the payment service
            // $currency = PaymentCurrency::where('iso_4217', $service['currency'])->first();
            // if ($currency) {
            //     $paymentService->paymentCurrencies()->attach($currency->id);
            // } else {
            //     $this->command->warn("Currency {$service['currency']} not found for {$service['name']}");
            // }
        }

        Auth::logout();
    }

    /**
     * Create a media object for the image and associate it with the payment service.
     */
    private function createAndAssociateImage(CardType $cardType, string $imageName, string $base_path = null)
    {
        if($base_path){
            $imagePath = $base_path . $imageName;

        }else{
            $imagePath = base_path('vendor/unusualify/modularity/resources/assets/images/card-types/' . $imageName);
        }
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
                $cardType->medias()->attach($media->id, [
                    'role' => 'logo',
                    'crop' => 'default',
                    'locale' => 'en',
                    'metadatas' => json_encode([
                        'alt_text' => $cardType->card_type . ' logo',
                        'caption' => $cardType->card_type . ' card type logo',
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
