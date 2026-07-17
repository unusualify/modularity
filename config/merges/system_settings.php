<?php

return [
    'name' => 'SystemSetting',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'System Settings',
    'cache_ttl' => (int) env('MODULAROUS_SYSTEM_SETTINGS_CACHE_TTL', 3600),
    /**
     * Fallback when SystemSettings `analytics.enabled` is unset in the DB.
     * Staging should leave DB off (or unset) so this stays false.
     * Prod: set analytics.enabled=true in System Settings (or MODULAROUS_ANALYTICS_ENABLED=true as seed default).
     */
    'analytics_enabled_default' => (bool) env('MODULAROUS_ANALYTICS_ENABLED', false),
    /**
     * When true, smtp.* from System Settings overrides config/mail.php.
     * Prefer .env MAIL_* unless you explicitly need CMS-managed SMTP.
     */
    'mail_override_enabled' => (bool) env('MODULAROUS_SYSTEM_SETTINGS_MAIL_OVERRIDE', false),
    'sensitive_keys' => [
        'smtp.password',
    ],
    'extra_inputs' => [],
    'input_overrides' => [],
    'hidden_inputs' => [],
    'input_aliases' => [
        '@system_social_links' => [
            'type' => 'json-repeater',
            'name' => 'social',
            'label' => 'Social Links',
            'default' => [],
            'draggable' => true,
            'orderKey' => 'position',
            'noHeaders' => true,
            'collapsible' => true,
            'collapsibleTitleField' => 'platform',
            'collapsibleDefaultOpen' => false,
            'schema' => [
                ['name' => 'platform', 'type' => 'text', 'label' => 'Platform', 'rules' => 'required'],
                ['name' => 'url', 'type' => 'text', 'label' => 'URL', 'rules' => 'required'],
                ['name' => 'icon', 'type' => 'image', 'label' => 'Icon', 'rules' => 'nullable'],
            ],
        ],
    ],
];
