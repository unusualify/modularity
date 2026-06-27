---
sidebarPos: 4
sidebarTitle: File Uploads
outline: deep
---

# Recipe — File Uploads

**Goal**: Let users upload files on a form and retrieve them on the model — with proper temp-file handling, max-count limits, and type filtering.

**Time**: ~5 minutes.

## Pick the Right Mechanism

Modularous offers three file options — choose based on your use case:

| Mechanism | When | Complexity |
|-----------|------|-----------|
| **Filepond** (`HasFileponds`) | Direct 1:N uploads (attachments, avatars) | Simplest |
| **Files** (`HasFiles`) | Shared file library reused across records | Medium |
| **Media** (`HasImages`) | Images with cropping / role / locale variants | Richest |

This recipe covers **Filepond**, which is right for 90% of cases. For the others, see [Files and Media](/guide/module-features/files-and-media).

## 1. Add the trait to your model

```php
use Unusualify\Modularous\Entities\Traits\HasFileponds;

class Ticket extends Model
{
    use HasFileponds;
}
```

This installs a `morphMany(Filepond::class, 'filepondable')` relation and the accessors `fileponds()`, `getFileponds()`, `hasFilepond()`.

## 2. Add the trait to your repository

```php
use Unusualify\Modularous\Repositories\Traits\FilepondsTrait;

class TicketRepository extends Repository
{
    use FilepondsTrait;
}
```

`FilepondsTrait` handles moving files from the temporary uploads table to permanent storage on save, and reverting on rollback.

## 3. Declare the input in your hydrate

```php
public function getInputs(): array
{
    return [
        ['type' => 'text', 'name' => 'title'],
        [
            'type'               => 'filepond',
            'name'               => 'attachments',
            'max'                => 5,
            'maxFileSize'        => '10MB',
            'acceptedExtensions' => ['pdf', 'doc', 'docx', 'png', 'jpg'],
        ],
    ];
}
```

See [input-filepond](/guide/form-inputs/input-filepond) for every option.

## 4. Use the files at runtime

### Listing

```php
$ticket = Ticket::find(1);

// All fileponds regardless of role
$ticket->fileponds;

// Fileponds scoped to a named role
$ticket->getFileponds('attachments');

// Boolean
if ($ticket->hasFilepond('attachments')) {
    // ...
}
```

### In Blade / email templates

```blade
@foreach ($ticket->getFileponds('attachments') as $file)
    <a href="{{ $file->url }}" download>{{ $file->name }}</a>
@endforeach
```

### In Vue / API responses

Files are automatically serialized when the model is appended with `fileponds`:

```php
// In your resource or repository
$invoice->append(['fileponds']);
```

## 5. Verify

1. Open the create form — you should see a drag-and-drop Filepond input.
2. Drop a file — a temporary row appears in `temporary_fileponds`.
3. Submit the form — the row moves to `fileponds` and the temp row is deleted.
4. Re-open the record — the file shows up attached.

## Variations

### Single-file avatar upload

```php
[
    'type'               => 'filepond',
    'name'               => 'avatar',
    'max'                => 1,
    'acceptedExtensions' => ['png', 'jpg', 'jpeg', 'webp'],
]
```

Or use the avatar preset input `input-filepond-avatar` for the circular crop UI. See [input-filepond-avatar](/guide/form-inputs/input-filepond-avatar).

### Document-only with strict size limit

```php
[
    'type'               => 'filepond',
    'name'               => 'documents',
    'max'                => 10,
    'maxFileSize'        => '5MB',
    'acceptedExtensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
]
```

### Cropable images with roles

Use `HasImages` + `input-image` instead. See [Files and Media — Media / Images](/guide/module-features/files-and-media#media-images).

## Housekeeping

Temporary Filepond rows that never get saved accumulate over time. Schedule the cleanup command:

```bash
php artisan modularous:flush:filepond
```

Or add it to `app/Console/Kernel.php`:

```php
$schedule->command('modularous:flush:filepond')->daily();
```

See [flush:filepond](/guide/console/flush/flush-filepond).

## Next Steps

- [File Storage with Filepond](/guide/generics/file-storage-with-filepond) — storage mechanics and database layout
- [Files and Media](/guide/module-features/files-and-media) — the full triple pattern
- [Uploader component](/guide/components/media/uploader) — the Vue upload widget
