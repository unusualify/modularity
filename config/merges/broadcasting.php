<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modularous Broadcasting Toggle
    |--------------------------------------------------------------------------
    |
    | Master switch for Modularous realtime features (Echo, ShouldBroadcast
    | events, notification broadcast channel, channel auth boot).
    | When false, broadcasting is skipped even if Reverb/Pusher are installed.
    | Default true. Also gated at runtime when the Pusher PHP SDK is missing
    | for pusher/reverb drivers — see BroadcastAvailability.
    |
    */
    'enabled' => filter_var(env('MODULAROUS_BROADCAST_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
];
