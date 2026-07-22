<?php

use Illuminate\Support\Facades\Broadcast;
use Unusualify\Modularous\Facades\Modularous;

/*
|--------------------------------------------------------------------------
| Modularous Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
| Guards: Modularous panel sessions use the `modularous` guard (not `web`).
| Listing `web` first would authorize against App\Models\User when present
| and cause private-users.{id} to 403 for Modularous user ids.
|
| Presence channel `editing.{modelType}.{modelId}`: modelType is an encoded
| FQCN (backslashes → dashes), e.g. Modules-Blog-Entities-Post. Laravel's
| `{param}` matcher is `[^\.]+`, so dashes are fine; dots are not.
|
*/

$modularousGuard = Modularous::getAuthGuardName();
$broadcastGuards = ['guards' => [$modularousGuard]];

Broadcast::channel('users.{userId}', function ($user, $userId) {
    if ($user === null) {
        return false;
    }

    return (int) $user->id === (int) $userId;
}, $broadcastGuards);

Broadcast::channel('models.{modelId}', function ($user, $modelId) {
    return $user !== null;
}, $broadcastGuards);

Broadcast::channel('chats.{chatId}', function ($user, $chatId) {
    return $user !== null;
}, $broadcastGuards);

Broadcast::channel('editing.{modelType}.{modelId}', function ($user, $modelType, $modelId) {
    if ($user === null) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => trim(($user->name ?? '') . ' ' . ($user->surname ?? '')),
    ];
}, $broadcastGuards);
