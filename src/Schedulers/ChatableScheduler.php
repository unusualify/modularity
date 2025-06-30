<?php

namespace Unusualify\Modularity\Schedulers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularity\Facades\ModularityFinder;

class ChatableScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularity:scheduler:chatable';

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
            $models = ModularityFinder::getModelsWithTrait(\Unusualify\Modularity\Entities\Traits\Chatable::class);

            foreach ($models as $model) {
                $model::hasNotifiableMessage()->chunk(100, function ($items) {
                    foreach ($items as $item) {
                        $item->handleChatableNotification();
                    }
                });
            }
        } catch (\Throwable $th) {
            Log::channel('scheduler')
                ->error('Modularity: Chatable scheduler error', [
                    'error' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                ]);
        }

    }
}
