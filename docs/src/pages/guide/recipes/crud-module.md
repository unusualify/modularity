---
sidebarPos: 2
sidebarTitle: CRUD Module
outline: deep
---

# Recipe — CRUD Module

**Goal**: Ship a working admin CRUD (list, create, edit, delete) for a new entity, wired up to permissions and the module sidebar.

**Time**: ~10 minutes.

## Prerequisites

- You have Modularous installed and can run `php artisan modularous:list`.
- You have a module in mind — we'll use **`Billing`** and an entity called **`Invoice`** for this recipe.

## 1. Scaffold the module

```bash
php artisan modularous:make:module Billing invoices \
  --fields="number:string,total:decimal,issued_at:datetime" \
  --traits="HasSlug" \
  --test
```

This creates:

- `modules/Billing/Entities/Invoice.php` — model with the listed fields + `HasSlug` trait
- `modules/Billing/Repositories/InvoiceRepository.php` — repository
- `modules/Billing/Http/Controllers/InvoiceController.php` — CRUD controller
- `modules/Billing/Http/Requests/*` — store / update form requests
- `modules/Billing/Database/Migrations/*_create_invoices_table.php` — migration
- `modules/Billing/Hydrates/Inputs/InvoiceHydrate.php` — form schema hydrate
- Route entries in `routes/admin.php` (or the module's route file)

See [make:module](/guide/console/generators/make-module) for every flag.

## 2. Run the migration

```bash
php artisan modularous:migrate
```

## 3. Register the sidebar entry

In the module's config (typically `modules/Billing/Config/billing.php`):

```php
'sidebar' => [
    'invoices' => [
        'title'   => 'Invoices',
        'icon'    => 'receipt_long',
        'route'   => 'admin.billing.invoices.index',
        'allowedRoles' => ['admin', 'accounting'],
    ],
],
```

Role filtering uses the [Allowable](/guide/generics/allowable) generic.

## 4. Create permissions

```bash
php artisan modularous:create:route:permissions Billing invoices
```

This generates Spatie permission records for `index / create / update / destroy` and attaches them to the default admin role.

See [create:route:permissions](/guide/console/generators/create-route-permissions).

## 5. Add custom fields to the form

Open `modules/Billing/Hydrates/Inputs/InvoiceHydrate.php` and adjust the schema returned by `getInputs()`. Typical additions:

```php
public function getInputs(): array
{
    return [
        ['type' => 'text',     'name' => 'number',    'label' => 'Invoice #'],
        ['type' => 'price',    'name' => 'total',     'label' => 'Total'],
        ['type' => 'date',     'name' => 'issued_at', 'label' => 'Issued'],
        ['type' => 'textarea', 'name' => 'notes'],
    ];
}
```

See [Hydrates](/system-reference/hydrates) for the schema contract and [Form Inputs](/guide/form-inputs/overview) for every input type.

## 6. Warm caches

```bash
php artisan modularous:cache:warm Billing
```

## 7. Verify

1. Log in to the admin panel as an admin user.
2. Click **Invoices** in the sidebar — you should see an empty data table.
3. Click **Create** — the form should render the fields from step 5.
4. Save — the record appears in the table; edit/delete actions work.

## Common Variations

### Add a relationship

```bash
php artisan modularous:make:route Billing invoices \
  --relationships="Customer:belongsTo"
```

See [Relationships](/guide/generics/relationships) for belongsToMany pivots.

### Add file attachments

Add `HasFileponds` to the model and an input:

```php
// Invoice.php
use HasFileponds;

// InvoiceHydrate.php
['type' => 'filepond', 'name' => 'attachments', 'max' => 5]
```

See [File Uploads recipe](./file-uploads) for full walkthrough.

### Add a state workflow

See [State Machine recipe](./state-machine).

## Next Steps

- [Module Features](/guide/module-features/overview) — stack traits for richer behaviour
- [Data Tables](/guide/components/shared/data-tables) — customise the list view
- [Repositories](/system-reference/repositories) — lifecycle hooks (`hydrate`, `afterSave`)
