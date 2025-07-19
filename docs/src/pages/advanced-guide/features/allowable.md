---
outline: deep
sidebarPos: 6
---

# Allowable

`Modularity` provides an `Allowable` trait that automatically handles role-based access control for arrays and collections. This trait integrates seamlessly with Laravel's authentication system to filter items based on user roles and permissions, ensuring only authorized content is displayed to users.

## How It Works

The `Allowable` trait scans array items for an `allowedRoles` key and automatically filters out items that the current authenticated user doesn't have permission to access. This allows you to declaratively control access to UI elements, menu items, actions, and other content based on user roles.

## Basic Usage

### Array Configuration

To use role-based filtering on arrays, simply add an `allowedRoles` key to your array items:

```php
$menuItems = [
    [
        'title' => 'Dashboard',
        'icon' => 'dashboard',
        'route' => 'dashboard'
    ],
    [
        'title' => 'User Management',
        'icon' => 'people',
        'route' => 'users.index',
        'allowedRoles' => ['admin', 'manager']
    ],
    [
        'title' => 'System Settings',
        'icon' => 'settings',
        'route' => 'settings',
        'allowedRoles' => ['admin']
    ]
];

// Filter items based on current user's roles
$allowedItems = $this->getAllowableItems($menuItems);
```

### Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use Unusualify\Modularity\Traits\Allowable;

class NavigationController extends Controller
{
    use Allowable;

    public function getNavigationItems()
    {
        $items = [
            [
                'title' => 'Home',
                'route' => 'home',
                'icon' => 'home'
            ],
            [
                'title' => 'Admin Panel',
                'route' => 'admin.dashboard',
                'icon' => 'admin_panel_settings',
                'allowedRoles' => ['admin', 'super-admin']
            ],
            [
                'title' => 'Reports',
                'route' => 'reports.index',
                'icon' => 'assessment',
                'allowedRoles' => ['manager', 'admin']
            ]
        ];

        return $this->getAllowableItems($items);
    }
}
```

## Core Methods

### getAllowableItems()

Filters an array or collection of items based on the current user's roles:

```php
public function getAllowableItems($items, $searchKey = null, $orClosure = null, $andClosure = null): array|Collection
```

**Parameters:**
- `$items` - Array or Collection to filter
- `$searchKey` - Key to search for roles (default: 'allowedRoles')
- `$orClosure` - Additional logic for allowing items (optional)
- `$andClosure` - Additional logic for filtering items (optional)

### isAllowedItem()

Checks if a single item is allowed for the current user:

```php
public function isAllowedItem($item, $searchKey = null, $orClosure = null, $andClosure = null, $disallowIfUnauthenticated = true): bool
```

### setAllowableUser()

Sets the user to check permissions against:

```php
public function setAllowableUser($user = null)
```

## Configuration Options

### 1. Basic Role Filtering

Filter items based on user roles:

```php
[
    'title' => 'Admin Dashboard',
    'route' => 'admin.dashboard',
    'allowedRoles' => ['admin'] // Only admins can see this
]
```

### 2. Multiple Roles

Allow multiple roles to access an item:

```php
[
    'title' => 'Content Management',
    'route' => 'content.index',
    'allowedRoles' => ['admin', 'editor', 'content-manager']
]
```

### 3. String Format Roles

Roles can be specified as comma-separated strings:

```php
[
    'title' => 'User Reports',
    'route' => 'reports.users',
    'allowedRoles' => 'admin,manager,supervisor'
]
```

### 4. Custom Search Key

Use a custom key for role definitions:

```php
$items = [
    [
        'title' => 'Special Feature',
        'permissions' => ['admin', 'special-access']
    ]
];

$allowedItems = $this->getAllowableItems($items, 'permissions');
```

## Advanced Usage

### Custom Logic with Closures

Add custom logic for allowing or restricting items:

```php
$items = [
    [
        'title' => 'Project Management',
        'route' => 'projects.index',
        'allowedRoles' => ['manager'],
        'project_id' => 123
    ]
];

// Allow item if user has role OR owns the project
$orClosure = function ($item, $user) {
    return isset($item['project_id']) && $user->owns_project($item['project_id']);
};

// Additional filtering logic
$andClosure = function ($item, $user) {
    return $user->is_active && !$user->is_suspended;
};

$allowedItems = $this->getAllowableItems($items, null, $orClosure, $andClosure);
```

### Working with Different Guards

Set up the trait to work with specific authentication guards:

```php
class AdminController extends Controller
{
    use Allowable;

    protected $allowableUserGuard = 'admin';

    public function getAdminMenuItems()
    {
        $items = [
            [
                'title' => 'System Monitor',
                'allowedRoles' => ['system-admin']
            ]
        ];

        return $this->getAllowableItems($items);
    }
}
```

### Custom Search Key Property

Define a default search key for the entire class:

```php
class MenuController extends Controller
{
    use Allowable;

    protected $allowedRolesSearchKey = 'requiredRoles';

    public function getMenuItems()
    {
        $items = [
            [
                'title' => 'Admin Area',
                'requiredRoles' => ['admin'] // Uses custom search key
            ]
        ];

        return $this->getAllowableItems($items);
    }
}
```

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
        'title' => 'Users',
        'route' => 'users.index',
        'icon' => 'people',
        'allowedRoles' => ['admin', 'user-manager']
    ],
    [
        'title' => 'Settings',
        'route' => 'settings.index',
        'icon' => 'settings',
        'allowedRoles' => ['admin']
    ],
    [
        'title' => 'Reports',
        'route' => 'reports.index',
        'icon' => 'assessment',
        'allowedRoles' => ['admin', 'manager', 'analyst']
    ]
];

$allowedNavigation = $this->getAllowableItems($navigationItems);
```

### Table Actions

```php
$tableActions = [
    [
        'title' => 'View',
        'icon' => 'visibility',
        'action' => 'view'
    ],
    [
        'title' => 'Edit',
        'icon' => 'edit',
        'action' => 'edit',
        'allowedRoles' => ['admin', 'editor']
    ],
    [
        'title' => 'Delete',
        'icon' => 'delete',
        'action' => 'delete',
        'allowedRoles' => ['admin']
    ],
    [
        'title' => 'Approve',
        'icon' => 'check_circle',
        'action' => 'approve',
        'allowedRoles' => ['admin', 'supervisor']
    ]
];

$allowedActions = $this->getAllowableItems($tableActions);
```

### Form Fields

```php
$formFields = [
    [
        'name' => 'title',
        'type' => 'text',
        'label' => 'Title'
    ],
    [
        'name' => 'content',
        'type' => 'textarea',
        'label' => 'Content'
    ],
    [
        'name' => 'status',
        'type' => 'select',
        'label' => 'Status',
        'allowedRoles' => ['admin', 'editor']
    ],
    [
        'name' => 'priority',
        'type' => 'select',
        'label' => 'Priority',
        'allowedRoles' => ['admin', 'manager']
    ]
];

$allowedFields = $this->getAllowableItems($formFields);
```

### Dashboard Widgets

```php
$dashboardWidgets = [
    [
        'title' => 'Overview',
        'component' => 'OverviewWidget',
        'size' => 'full'
    ],
    [
        'title' => 'User Statistics',
        'component' => 'UserStatsWidget',
        'size' => 'half',
        'allowedRoles' => ['admin', 'manager']
    ],
    [
        'title' => 'System Health',
        'component' => 'SystemHealthWidget',
        'size' => 'half',
        'allowedRoles' => ['admin', 'system-admin']
    ],
    [
        'title' => 'Revenue Chart',
        'component' => 'RevenueChartWidget',
        'size' => 'full',
        'allowedRoles' => ['admin', 'finance-manager']
    ]
];

$allowedWidgets = $this->getAllowableItems($dashboardWidgets);
```

## Authentication Handling

### Unauthenticated Users

By default, unauthenticated users are denied access to items with role restrictions:

```php
// Default behavior - deny unauthenticated users
$isAllowed = $this->isAllowedItem($item); // false if not authenticated

// Allow unauthenticated users
$isAllowed = $this->isAllowedItem($item, null, null, null, false);
```

### Custom User

Set a specific user for permission checking:

```php
$specificUser = User::find(123);
$this->setAllowableUser($specificUser);

$allowedItems = $this->getAllowableItems($items);
```

## Integration with Other Traits

### Combined with ResponsiveVisibility

```php
class MenuController extends Controller
{
    use Allowable, ResponsiveVisibility;

    public function getMenuItems()
    {
        $items = [
            [
                'title' => 'Admin Panel',
                'route' => 'admin.dashboard',
                'allowedRoles' => ['admin'],
                'responsive' => [
                    'hideBelow' => 'md'
                ]
            ],
            [
                'title' => 'Mobile Admin',
                'route' => 'admin.mobile',
                'allowedRoles' => ['admin'],
                'responsive' => [
                    'showOn' => ['sm', 'md']
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

## Error Handling

The trait includes built-in validation and error handling:

```php
// Invalid items type
try {
    $this->getAllowableItems('invalid-type');
} catch (\Exception $e) {
    // Exception: Invalid items type, must be an array or a collection
}

// Works with both arrays and collections
$arrayItems = $this->getAllowableItems($itemsArray);
$collectionItems = $this->getAllowableItems(collect($itemsArray));
```

## Best Practices

### 1. Consistent Role Naming

Use consistent role naming conventions across your application:

```php
// Good
'allowedRoles' => ['admin', 'user-manager', 'content-editor']

// Avoid
'allowedRoles' => ['Admin', 'userManager', 'content_editor']
```

### 2. Hierarchical Permissions

Consider role hierarchies when defining permissions:

```php
$items = [
    [
        'title' => 'Basic Feature',
        'allowedRoles' => ['user', 'manager', 'admin']
    ],
    [
        'title' => 'Advanced Feature',
        'allowedRoles' => ['manager', 'admin']
    ],
    [
        'title' => 'Admin Only',
        'allowedRoles' => ['admin']
    ]
];
```

### 3. Performance Considerations

For large datasets, consider caching allowed items:

```php
public function getAllowedMenuItems()
{
    $cacheKey = 'menu_items_' . auth()->id();
    
    return cache()->remember($cacheKey, 3600, function () {
        $items = $this->getMenuItems();
        return $this->getAllowableItems($items);
    });
}
```

### 4. Testing Permissions

Always test permission logic with different user roles:

```php
// Test with different users
$adminUser = User::factory()->create(['role' => 'admin']);
$regularUser = User::factory()->create(['role' => 'user']);

$this->setAllowableUser($adminUser);
$adminItems = $this->getAllowableItems($items);

$this->setAllowableUser($regularUser);
$userItems = $this->getAllowableItems($items);
```

::: info

The trait automatically handles both array and object items, preserving the original data structure while filtering based on permissions.

:::

::: tip

Items without an `allowedRoles` key are considered public and will be included for all users, including unauthenticated ones.

:::

::: warning

Always validate that your role-checking logic (`hasRole()` method) is properly implemented on your User model to ensure the trait works correctly.

:::

## Security Considerations

### 1. Server-Side Filtering

Always filter sensitive data on the server side:

```php
// Good - filter before sending to frontend
$allowedItems = $this->getAllowableItems($items);
return response()->json($allowedItems);

// Bad - don't rely on frontend filtering
return response()->json($items); // Client can access all items
```

### 2. Role Validation

Ensure your User model properly validates roles:

```php
// In your User model
public function hasRole($roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return $this->roles()->whereIn('name', $roles)->exists();
}
```

### 3. Logging Access

Consider logging access attempts for audit purposes:

```php
$orClosure = function ($item, $user) {
    $hasSpecialAccess = $user->hasSpecialAccess($item);
    
    if ($hasSpecialAccess) {
        Log::info('Special access granted', [
            'user_id' => $user->id,
            'item' => $item['title']
        ]);
    }
    
    return $hasSpecialAccess;
};
```

The `Allowable` trait provides a powerful and flexible way to implement role-based access control in your Laravel applications, ensuring that users only see and can interact with content they're authorized to access.
