<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Http\Controllers\ChatController;
use Unusualify\Modularity\Http\Controllers\ProcessController;
use Unusualify\Modularity\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
| Middlewares : [ 'web.auth', ...\Unusualify\Modularity\Facades\ModularityRoutes::defaultMiddlewares(), \Unusualify\Modularity\Facades\ModularityRoutes::defaultPanelMiddlewares()]
|
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/email/verify/{id}/{hash}', 'VerificationController@verify')
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/email/verification-notification', 'VerificationController@send')
    ->middleware(['throttle:6,1,email-verification'])
    ->name('verification.send');

Route::prefix('register')->as('register.')
    ->withoutMiddleware(['modularity.panel', 'web.auth', 'modularity.core'])
    ->middleware(['web', 'modularity.core'])
    ->group(function () {

        Route::get('/password/generate/{token}', 'PasswordController@showForm')
            ->name('password.generate.form');
        Route::post('/password/generate', 'PasswordController@savePassword')
            ->name('password.generate');
    });

Route::singleton('profile', 'ProfileController', ['names' => ['edit' => 'profile']]);
Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
    Route::get('show', [ProfileController::class, 'display'])->name('show');
});
Route::put('profile/company', 'ProfileController@updateCompany')->name('profile.company');

Route::resource('', 'DashboardController', ['as' => 'dashboard', 'names' => ['index' => 'dashboard']])->only(['index']);

Route::get('users/impersonate/stop', 'ImpersonateController@stopImpersonate')->name('impersonate.stop');
Route::get('users/impersonate/{id}', 'ImpersonateController@impersonate')->name('impersonate');

Route::get('tests/notifications/{notification}', function ($notification) {
    if(!@class_exists($notification)) {
        return 'Notification class not found';
    }

    $reflector = new \ReflectionClass($notification);

    if($reflector->isAbstract() || $reflector->isInterface() || $reflector->isTrait()) {
        return 'Notification class is abstract, interface or trait';
    }

    // get the constructor parameters
    $constructor = $reflector->getConstructor();
    $constructorParameters = $constructor->getParameters();
    $arguments = [];

    foreach($constructorParameters as $parameter) {
        if($parameter->isOptional()) {
            $arguments[] = $parameter->getDefaultValue();
            continue;
        }

        if($parameter->getName() === 'token') {
            $arguments[] = uniqid();
            continue;
        }

        if(@class_exists($parameter->getType()->getName())) {
            $reflector = new \ReflectionClass($parameter->getType()->getName());

            // $reflector is subclass of \Illuminate\Database\Eloquent\Model
            if($reflector->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) {
                $class = $parameter->getType()->getName();
                $instance = $class::first();

                if(!$instance) {
                    throw new \Exception('Any instance of ' . $class . ' not found');
                }

                $arguments[] = $instance;
                continue;
            }
            dd($instance, $arguments);
            $instance = $reflector->newInstanceWithoutConstructor();
            dd($instance);
            $arguments[] = \Unusualify\Modularity\Entities\User::role(['superadmin'], Modularity::getAuthGuardName())->first();

            continue;
        }
    }

    $notification = new $notification(...$arguments);

    $notification = $notification->toMail(\Unusualify\Modularity\Entities\User::role(['superadmin'], Modularity::getAuthGuardName())->first());

    echo $notification->subject;

    echo $notification->render();

    die();
})->name('tests.notification.show');
Route::get('tests/notifications', function () {

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

    return implode('<br>', $notificationLinks);
})->name('tests.notification.index');


// system internal api routes (for ajax web routes)
Route::prefix('api')->group(function () {
    Route::group(['prefix' => 'chatable', 'as' => 'chatable.', 'controller' => ChatController::class], function () {
        Route::get('{chat}', 'index')->name('index');
        Route::get('{chat}/attachments', 'attachments')->name('attachments');
        Route::get('{chat}/pinned-message', 'pinnedMessage')->name('pinned-message');
        Route::post('{chat}', 'store')->name('store');
        Route::put('{chat_message}', 'update')->name('update');
        Route::get('show/{chat_message}', 'show')->name('show');
        Route::delete('destroy/{chat_message}', 'destroy')->name('destroy');
    });

    Route::group(['prefix' => 'process', 'as' => 'process.'], function () {
        Route::get('{process}', [ProcessController::class, 'show'])->name('show');
        Route::put('{process}', [ProcessController::class, 'update'])->name('update');
    });

    if (modularityConfig('enabled.media-library')) {
        Route::group(['prefix' => 'media-library', 'as' => 'media-library.'], function () {
            Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'MediaLibraryController@signS3Upload']);
            Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'MediaLibraryController@signAzureUpload']);
            Route::put('medias/single-update', ['as' => 'media.single-update', 'uses' => 'MediaLibraryController@singleUpdate']);
            Route::put('medias/bulk-update', ['as' => 'media.bulk-update', 'uses' => 'MediaLibraryController@bulkUpdate']);
            Route::put('medias/bulk-delete', ['as' => 'media.bulk-delete', 'uses' => 'MediaLibraryController@bulkDelete']);
            Route::get('medias/tags', ['as' => 'media.tags', 'uses' => 'MediaLibraryController@tags']);
            Route::resource('medias', 'MediaLibraryController', ['names' => 'media', 'only' => ['index', 'store', 'destroy']]);
        });
    }

    if (modularityConfig('enabled.file-library')) {
        Route::group(['prefix' => 'file-library', 'as' => 'file-library.'], function () {
            Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'FileLibraryController@signS3Upload']);
            Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'FileLibraryController@signAzureUpload']);
            Route::put('files/single-update', ['as' => 'file.single-update', 'uses' => 'FileLibraryController@singleUpdate']);
            Route::put('files/bulk-update', ['as' => 'file.bulk-update', 'uses' => 'FileLibraryController@bulkUpdate']);
            Route::put('files/bulk-delete', ['as' => 'file.bulk-delete', 'uses' => 'FileLibraryController@bulkDelete']);
            Route::get('files/tags', ['as' => 'file.tags', 'uses' => 'FileLibraryController@tags']);
            Route::resource('files', 'FileLibraryController', ['names' => 'file', 'only' => ['index', 'store', 'destroy']]);
        });
    }
});

Route::post('modularity/metrics', 'MetricController')->name('modularity.metrics');
