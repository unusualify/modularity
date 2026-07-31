<?php

declare(strict_types=1);

return [
    'home' => 'Go to homepage',

    '404' => [
        'meta_title' => 'Page not found',
        'title' => 'Page not found',
        'description' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
    ],

    '403' => [
        'meta_title' => 'Forbidden',
        'title' => 'Access denied',
        'description' => 'You do not have permission to view this page.',
    ],

    '500' => [
        'meta_title' => 'Server error',
        'title' => 'Something went wrong',
        'description' => 'An unexpected error occurred. Please try again later.',
    ],
];
