<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Symfony\Component\Finder\Finder;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\Traits\ManageUtilities;
use Unusualify\Modularity\Traits\Allowable;
use Unusualify\Modularity\View\Component;

class ListController extends BaseController
{
    use ManageUtilities, Allowable;

    /**
     * @var string
     */
    protected $moduleName = 'List';

    /**
     * @var string
     */
    protected $routeName = 'List';

    public function __construct(\Illuminate\Foundation\Application $app, Request $request)
    {
        parent::__construct(
            $app,
            $request
        );

        $this->removeMiddleware("can:{$this->permissionPrefix()}_" . Permission::VIEW->value);
        $this->middleware('can:tests.notifications', ['only' => ['index']]);
    }

    public function index($parentId = null)
    {
        $notificationPaths = [];

        if(file_exists(base_path('app/Notifications'))) {
            $notificationPaths[] = base_path('app/Notifications');
        }
        if(file_exists(Modularity::getVendorPath('/src/Notifications'))) {
            $notificationPaths[] = Modularity::getVendorPath('/src/Notifications');
        }

        $allModules = Modularity::all();

        foreach ($allModules as $module) {
            if (file_exists($moduleNotificationsPath = $module->getTargetClassPath('notifications'))) {
                $notificationPaths[] = $moduleNotificationsPath;
            }
        }

        // get all classes in the notification paths but not abstract or interface or trait
        $notificationClasses = collect();

        collect(Finder::create()->files()->depth(0)->in($notificationPaths))->reduce(function ($carry, $file) {
            $content = get_file_string($file->getRealPath());
            $className = get_file_class($file->getRealPath());

            if ($className) {
                $reflector = new \ReflectionClass(get_file_class($file->getRealPath()));
                if (! $reflector->isAbstract() && ! $reflector->isInterface() && ! $reflector->isTrait()) {
                    $carry[$className] = $file;
                }
            }

            return $carry;
        }, $notificationClasses);


        // create for each notification class a anchor link list
        $notificationLinks = [];
        foreach ($notificationClasses as $notificationClass => $file) {
            $notificationLinks[] = '<a href="' . route('admin.tests.notification.show', $notificationClass) . '">' . $file . '</a>';
        }

        $view = Collection::make([
            "$this->baseKey::layouts.index",
            "$this->baseKey::layouts.index",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $indexData);

        return View::make('modularity::tests.notifications.index', compact('notificationLinks'));
    }
}
