---
outline: deep
sidebarPos: 6
---

# Responsive Visibility

`Modularity` provides a `ResponsiveVisibility` trait that automatically handles responsive display classes for arrays and collections. This trait integrates seamlessly with Vuetify's responsive utility classes to control when UI elements are shown or hidden based on screen size breakpoints.

## How It Works

The `ResponsiveVisibility` trait scans array items for a `responsive` key and automatically applies appropriate Vuetify classes (`d-none`, `d-sm-none`, `d-lg-flex`, `d-xxl-block`, etc.) to the item's `class` attribute. This allows you to declaratively control visibility across different screen sizes without manually managing CSS classes.

## Basic Usage

### Array Configuration

To use responsive visibility on arrays, simply add a `responsive` key to your array items:

```php
$menuItems = [
    [
        'title' => 'Dashboard',
        'icon' => 'dashboard',
        'class' => 'primary-nav'
    ],
    [
        'title' => 'Mobile Menu',
        'icon' => 'menu',
        'responsive' => [
            'showOn' => ['sm', 'md']
        ]
    ],
    [
        'title' => 'Desktop Settings',
        'icon' => 'settings',
        'responsive' => [
            'hideBelow' => 'lg'
        ]
    ]
];

// Apply responsive classes (default display: flex)
$responsiveItems = $this->getResponsiveItems($menuItems);
```

### Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use Unusualify\Modularity\Traits\ResponsiveVisibility;

class NavigationController extends Controller
{
    use ResponsiveVisibility;

    public function getNavigationItems()
    {
        $items = [
            [
                'title' => 'Home',
                'route' => 'home',
                'icon' => 'home'
            ],
            [
                'title' => 'Quick Actions',
                'route' => 'quick-actions',
                'icon' => 'flash_on',
                'responsive' => [
                    'hideOn' => ['sm'] // Hide on small screens
                ]
            ],
            [
                'title' => 'Menu Toggle',
                'action' => 'toggleMenu',
                'icon' => 'menu',
                'responsive' => [
                    'showOn' => ['sm', 'md'] // Show only on small/medium screens
                ]
            ]
        ];

        return $this->getResponsiveItems($items);
    }
}
```

## Display Types

The trait supports different CSS display types when showing elements:

- `flex` (default) - Uses `d-{breakpoint}-flex`
- `block` - Uses `d-{breakpoint}-block`
- `inline-block` - Uses `d-{breakpoint}-inline-block`
- `inline` - Uses `d-{breakpoint}-inline`

### Custom Display Type

You can specify a custom display type when applying responsive classes:

```php
$items = [
    [
        'title' => 'Block Element',
        'responsive' => [
            'showOn' => ['lg', 'xl']
        ]
    ]
];

// Apply with block display
$responsiveItems = $this->getResponsiveItems($items);

// Or apply to individual items with custom display
$processedItem = $this->applyResponsiveClasses($item, null, 'block');
```

## Configuration Options

### 1. hideOn - Hide on Specific Breakpoints

Hide elements on specific screen sizes:

```php
[
    'title' => 'Desktop Action',
    'responsive' => [
        'hideOn' => ['sm', 'md'] // Adds: d-sm-none d-md-none
    ]
]
```

### 2. showOn - Show Only on Specific Breakpoints

Show elements only on specified screen sizes (hidden by default):

```php
[
    'title' => 'Large Screen Widget',
    'responsive' => [
        'showOn' => ['lg', 'xl'] // Adds: d-none d-lg-flex d-xl-flex
    ]
]
```

### 3. hideBelow - Hide Below Breakpoint

Hide elements below a certain screen size:

```php
[
    'title' => 'Advanced Features',
    'responsive' => [
        'hideBelow' => 'md' // Adds: d-none d-md-flex
    ]
]
```

### 4. hideAbove - Hide Above Breakpoint

Hide elements above a certain screen size:

```php
[
    'title' => 'Mobile-First Feature',
    'responsive' => [
        'hideAbove' => 'md' // Adds: d-lg-none d-xl-none d-xxl-none
    ]
]
```

### 5. breakpoints - Fine-Grained Control

Specify exact visibility for each breakpoint:

```php
[
    'title' => 'Custom Visibility',
    'responsive' => [
        'breakpoints' => [
            'sm' => false,  // d-sm-none
            'md' => true,   // d-md-flex
            'lg' => true,   // d-lg-flex
            'xl' => false,  // d-xl-none
            'xxl' => true   // d-xxl-flex
        ]
    ]
]
```

## Available Breakpoints

The trait supports Vuetify's standard breakpoints:

- `sm` - Small screens (600px and up)
- `md` - Medium screens (960px and up)
- `lg` - Large screens (1264px and up)
- `xl` - Extra large screens (1904px and up)
- `xxl` - Extra extra large screens (2560px and up)

## Real-World Examples

### Navigation Menu

```php
$navigationItems = [
    [
        'title' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'dashboard'
    ],
    [
        'title' => 'Mobile Menu',
        'action' => 'openDrawer',
        'icon' => 'menu',
        'responsive' => [
            'hideAbove' => 'md' // Show only on mobile/tablet
        ]
    ],
    [
        'title' => 'Search',
        'component' => 'SearchField',
        'responsive' => [
            'hideBelow' => 'md' // Hide on mobile, show on desktop
        ]
    ],
    [
        'title' => 'User Profile',
        'component' => 'UserProfile',
        'responsive' => [
            'showOn' => ['lg', 'xl', 'xxl'] // Show only on large screens
        ]
    ]
];

$responsiveNav = $this->getResponsiveItems($navigationItems);
```

### Table Actions

```php
$tableActions = [
    [
        'title' => 'Edit',
        'icon' => 'edit',
        'action' => 'edit'
    ],
    [
        'title' => 'Delete',
        'icon' => 'delete',
        'action' => 'delete',
        'responsive' => [
            'hideOn' => ['sm'] // Hide delete button on mobile
        ]
    ],
    [
        'title' => 'More Actions',
        'icon' => 'more_vert',
        'action' => 'showMore',
        'responsive' => [
            'showOn' => ['sm'] // Show only on mobile as dropdown
        ]
    ]
];

$responsiveActions = $this->getResponsiveItems($tableActions);
```

### Form Fields with Block Display

```php
$formFields = [
    [
        'name' => 'title',
        'type' => 'text',
        'label' => 'Title'
    ],
    [
        'name' => 'description',
        'type' => 'textarea',
        'label' => 'Description',
        'responsive' => [
            'hideBelow' => 'md' // Hide detailed description on mobile
        ]
    ],
    [
        'name' => 'quick_note',
        'type' => 'text',
        'label' => 'Quick Note',
        'responsive' => [
            'showOn' => ['sm', 'md'] // Show simplified field on mobile
        ]
    ]
];

// Apply with block display for form fields
$responsiveFields = collect($formFields)->map(function ($field) {
    return $this->applyResponsiveClasses($field, null, 'block');
})->toArray();
```

## Advanced Usage

### Custom Search Key

You can customize the key used for responsive settings:

```php
$items = [
    [
        'title' => 'Custom Item',
        'visibility' => [
            'hideOn' => ['sm']
        ]
    ]
];

$responsiveItems = $this->getResponsiveItems($items, 'visibility');
```

### Individual Item Processing

Process individual items with custom display types:

```php
$item = [
    'title' => 'Test Item',
    'responsive' => [
        'showOn' => ['lg', 'xl']
    ]
];

// Apply with different display types
$flexItem = $this->applyResponsiveClasses($item, null, 'flex');
$blockItem = $this->applyResponsiveClasses($item, null, 'block');
$inlineItem = $this->applyResponsiveClasses($item, null, 'inline');
```

### Checking Responsive Settings

You can check if an item has responsive settings:

```php
$item = [
    'title' => 'Test Item',
    'responsive' => [
        'hideOn' => ['sm']
    ]
];

if ($this->hasResponsiveSettings($item)) {
    $processedItem = $this->applyResponsiveClasses($item);
}
```

## Display Type Examples

### Flex Display (Default)

```php
[
    'title' => 'Flex Container',
    'responsive' => [
        'showOn' => ['md', 'lg'] // Adds: d-none d-md-flex d-lg-flex
    ]
]
```

### Block Display

```php
[
    'title' => 'Block Element',
    'responsive' => [
        'hideBelow' => 'lg' // Adds: d-none d-lg-block
    ]
]

// Process with block display
$processedItem = $this->applyResponsiveClasses($item, null, 'block');
```

### Inline Display

```php
[
    'title' => 'Inline Element',
    'responsive' => [
        'showOn' => ['sm', 'md'] // Adds: d-none d-sm-inline d-md-inline
    ]
]

// Process with inline display
$processedItem = $this->applyResponsiveClasses($item, null, 'inline');
```

::: info

The trait automatically preserves existing CSS classes when adding responsive classes. If an item already has a `class` attribute, the responsive classes will be appended to it.

:::

::: tip

For optimal performance, apply responsive classes only to items that need them. Items without a `responsive` key will be returned unchanged.

:::

::: warning

The display parameter must be one of: `flex`, `block`, `inline-block`, or `inline`. Any other value will throw an exception.

:::

## Integration with Allowable Trait

The `ResponsiveVisibility` trait works seamlessly with the `Allowable` trait:

```php
class MenuController extends Controller
{
    use Allowable, ResponsiveVisibility;

    public function getMenuItems()
    {
        $items = [
            [
                'title' => 'Admin Panel',
                'route' => 'admin',
                'allowedRoles' => ['admin', 'manager'],
                'responsive' => [
                    'hideBelow' => 'md'
                ]
            ]
        ];

        // First filter by permissions, then apply responsive classes
        $allowedItems = $this->getAllowableItems($items);
        $responsiveItems = $this->getResponsiveItems($allowedItems);

        return $responsiveItems;
    }
}
```

This approach ensures that only authorized users see the menu items, and those items are displayed appropriately across different screen sizes with the correct display type.

## Error Handling

The trait includes built-in validation:

```php
// This will throw an exception
try {
    $this->applyResponsiveClasses($item, null, 'invalid-display');
} catch (\Exception $e) {
    // Exception: Invalid display value, must be one of: flex, block, inline-block, inline
}

// This will throw an exception
try {
    $this->getResponsiveItems('invalid-type');
} catch (\Exception $e) {
    // Exception: Invalid items type, must be an array or a collection
}
```

The trait ensures type safety and provides clear error messages when invalid parameters are provided.