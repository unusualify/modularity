<?php

namespace Unusualify\Modularous\Schedulers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularous\Entities\Traits\Chatable;
use Unusualify\Modularous\Facades\ModularousFinder;

class ChatableScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:scheduler:chatable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $models = ModularousFinder::getModelsWithTrait(Chatable::class);
            $processed = 0;

            foreach ($models as $model) {
                $model::hasNotifiableMessage()->chunk(100, function ($items) use (&$processed) {
                    foreach ($items as $item) {
                        $item->handleChatableNotification();
                        $processed++;
                    }
                });
            }

            Log::channel('scheduler')
                ->info('Modularous: Chatable scheduler completed', [
                    'models' => count($models),
                    'processed' => $processed,
                ]);
        } catch (\Throwable $th) {
            Log::channel('scheduler')
                ->error('Modularous: Chatable scheduler error', [
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                ]);
        }

    }
}
