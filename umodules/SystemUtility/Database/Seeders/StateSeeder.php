<?php

namespace Modules\SystemUtility\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class StateSeeder extends Seeder
{
    protected $mediaLibraryController;

    public function __construct()
    {

    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $states = [
            [
                'code' => 'pending-approval',
                'name' => 'Pending Approval',
                'color' => '#FFA500', // Orange
                'icon' => 'mdi-clock-outline',
                'published' => 1
            ],
            [
                'code' => 'cancelled',
                'name' => 'Cancelled',
                'color' => '#FF0000', // Red
                'icon' => 'mdi-close-circle-outline',
                'published' => 1
            ],
            [
                'code' => 'pending-translations',
                'name' => 'Pending Translations',
                'color' => '#4169E1', // Royal Blue
                'icon' => 'mdi-translate',
                'published' => 1
            ],
            [
                'code' => 'approved',
                'name' => 'Approved',
                'color' => '#008000', // Green
                'icon' => 'mdi-check-circle-outline',
                'published' => 1
            ],
            [
                'code' => 'unapproved',
                'name' => 'Unapproved',
                'color' => '#DC143C', // Crimson
                'icon' => 'mdi-alert-circle-outline',
                'published' => 1
            ],
            [
                'code' => 'pending-payment',
                'name' => 'Pending Payment',
                'color' => '#FFD700', // Gold
                'icon' => 'mdi-currency-usd',
                'published' => 1
            ],
            [
                'code' => 'published',
                'name' => 'Published',
                'color' => '#32CD32', // Lime Green
                'icon' => 'mdi-eye-outline',
                'published' => 1
            ],
            [
                'code' => 'completed',
                'name' => 'Completed',
                'color' => '#228B22', // Forest Green
                'icon' => 'mdi-check-all',
                'published' => 1
            ],
            [
                'code' => 'paid',
                'name' => 'paid',
                'color' => '#008000', // Green
                'icon' => 'mdi-check-circle-outline',
                'published' => 1
            ],
            [
                'code' => 'unpaid',
                'name' => 'unpaid',
                'color' => '#DC143C', // Crimson
                'icon' => 'mdi-alert-circle-outline',
                'published' => 1
            ],
        ];
        $stateRepository = App::make('Modules\SystemUtility\Repositories\StateRepository');;

        foreach ($states as $state){
            $stateRepository->create($state);
        }
    }
}
