<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Modularity Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('models.{modelId}', function ($user, $modelId) {
    return $user->id == $modelId;
    return $user->id === $modelType::findOrNew($modelId);
}, ['guards' => ['web', 'unusual_users']]);


