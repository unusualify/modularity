<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chat Message Edit Window
    |--------------------------------------------------------------------------
    |
    | Number of minutes after creation during which the sender can edit
    | a chat message. A message can be edited only once and only while
    | it has not been read by the other party.
    |
    */
    'edit_window' => (int) env('MODULAROUS_CHAT_EDIT_WINDOW', 15),

    /*
    |--------------------------------------------------------------------------
    | Maximum Pinned Messages
    |--------------------------------------------------------------------------
    |
    | Number of messages that can be pinned in a chat at the same time.
    | Pinning one more message unpins the oldest pin, after the user confirms it.
    |
    */
    'max_pinned_messages' => (int) env('MODULAROUS_CHAT_MAX_PINNED_MESSAGES', 4),

    //Pin Durations can be 24 hours, 7 days or 30 days.
    'pin_durations' => [24, 24 * 7, 24 * 30],
];
