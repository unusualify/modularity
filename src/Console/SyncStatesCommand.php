<?php

namespace Unusualify\Modularity\Console;

use Unusualify\Modularity\Facades\ModularityFinder;

use function Laravel\Prompts\select;

class SyncStatesCommand extends BaseCommand
{
    protected $hidden = true;

    /**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'modularity:sync:states
        {model? : The model to sync states}
    ';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a stateable model states';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        // check model class exists if defined
        if($this->argument('model')){
            $model = $this->argument('model');
            if(!class_exists($model)){
                $this->error($model . ' class does not exist');
                return 1;
            }
        }else{
            // get all stateable models
            $models = ModularityFinder::getModelsWithTrait(\Unusualify\Modularity\Entities\Traits\HasStateable::class);

            $model = select('Select a model', $models);
        }

        $newStates = $model::syncStateData();

        foreach ($newStates as $newState) {
            $this->info('New state created: ' . $newState->code);
        }

        return 0;
    }
}
