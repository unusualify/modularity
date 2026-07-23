---
sidebarPos: 2
sidebarTitle: FeatureNotification
---

# FeatureNotification

`Modules\SystemNotification\Notifications\FeatureNotification`

Abstract base class for all system notifications in the `SystemNotification` module. Extends Laravel's `Notification` and adds:

- **Multi-channel dispatch** with per-class config overrides
- **Callback hooks** to customise every part of the mail/database payload without subclassing
- **Database payload** with a consistent schema for the in-app notification centre
- **Queue routing** per channel (mail queue vs. database queue)
- **Failure logging** to a dedicated `modularous-notification-failure` log channel

All concrete system notifications (`ModelCreatedNotification`, `StateableUpdatedNotification`, etc.) extend this class.

---

## Constructor

```php
public function __construct(public Model $model)
```

Sets `$this->model` and generates a unique `$token` via `uniqid()`. The token is stored in the database payload and used to locate the notification record when building mail redirector URLs.

---

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$model` | `Model` | — | The Eloquent model the notification is about |
| `$token` | `string` | `uniqid()` | Unique token per notification instance |
| `$modelHeadline` | `string\|null` | `null` | Override the human-readable model name |
| `$modelTitleField` | `string\|null` | `null` | Override the model title field resolver |
| `$salutationMessage` | `string\|null` | `null` | Override the mail salutation text |
| `$validChannels` | `array` | `['mail','database','broadcast','vonage','slack']` | Channels that are accepted for dispatch |
| `$defaultChannels` | `array` | `[]` | Default channels when no config override is present |
| `$outroLines` | `array` | `[]` | Extra lines appended at the bottom of the mail body |

---

## Channel Resolution

`via()` resolves channels in this order:

1. Check `config("modularous.notifications.{FullyQualifiedClassName}.channels")` — comma-separated string.
2. Fall back to `$defaultChannels` on the subclass.

Invalid channel names (not in `$validChannels`) are filtered out automatically.

```php
// Override channels for a specific notification via config:
// config/modularous.php
'notifications' => [
    \Modules\SystemNotification\Notifications\StateableUpdatedNotification::class => [
        'channels' => 'mail,database',
    ],
],
```

---

## Queue Routing

```php
public function viaConnections(): array
// mail → modularousConfig('notifications.mail_connection')
// database → modularousConfig('notifications.database_connection')

public function viaQueues(): array
// mail → modularousConfig('notifications.mail_queue')
// database → modularousConfig('notifications.database_queue')
```

---

## Callback System

Every part of the notification output can be replaced via static callbacks registered on the specific subclass **or** on `FeatureNotification` itself (acts as a global override).

### Available Callbacks

| Static method | Callback signature | What it replaces |
|---------------|--------------------|-----------------|
| `createModuleRouteHeadline(callable)` | `fn(Model): string` | Human-readable model name in subject/body |
| `createModelTitleField(callable)` | `fn(Model): string` | The model's display title (name/title/slug/id) |
| `createSubject(callable)` | `fn(notifiable, Model, string $default): string` | In-app notification subject |
| `createMailSubject(callable)` | `fn(notifiable, Model, string $default): string` | Mail `subject:` line |
| `createMessage(callable)` | `fn(notifiable, Model, string $default): string` | Plain-text notification body |
| `createHtmlMessage(callable)` | `fn(notifiable, Model, string $default): string` | HTML notification body |
| `createMailMessage(callable)` | `fn(notifiable, Model, string $default): string` | Mail body line |
| `createActionText(callable)` | `fn(notifiable, Model, string $default): string` | Button label in the notification centre |
| `createMailActionText(callable)` | `fn(notifiable, Model, string $default): string` | Button label in the mail |
| `createMailSalutation(callable)` | `fn(string $default): string` | Mail salutation |
| `createDatabaseFeatureFields(callable)` | `fn(array $fields, notifiable, Model): array` | Merge extra fields into `toDatabase()` payload |
| `updateLaravelMailMessage(callable)` | `fn(MailMessage, notifiable, Model): MailMessage` | Post-process the entire `MailMessage` object |

### Example: Override the mail subject for one notification type

```php
use Modules\SystemNotification\Notifications\StateableUpdatedNotification;

StateableUpdatedNotification::createMailSubject(
    fn($notifiable, $model, $default) => "Status update for {$model->name}"
);
```

### Example: Add an extra line to every FeatureNotification mail

```php
use Modules\SystemNotification\Notifications\FeatureNotification;

FeatureNotification::createMailSalutation(fn($default) => 'Thanks, The Team');
```

---

## Database Payload

`toDatabase()` returns the array stored in `notifications.data`. The base structure is built by `toDatabaseFeatureFields()`:

```php
[
    'token'          => string,   // uniqid, used for mail redirector lookup
    'subject'        => string,   // notification centre subject
    'message'        => string,   // plain text body
    'htmlMessage'    => string,   // HTML body
    'redirectorText' => string,   // action button label
    'redirector'     => string|null, // URL to the relevant admin panel page
    'hasRedirector'  => bool,
]
```

Use `createDatabaseFeatureFields` to merge additional keys:

```php
StateableUpdatedNotification::createDatabaseFeatureFields(
    fn($fields, $notifiable, $model) => ['old_state' => $model->previousState]
);
```

---

## Redirector Resolution

`getNotificationRedirector()` builds an admin panel URL pointing to the model's edit or index page. It uses `Modularous::find($moduleName)->getRouteActionUrl(...)` and respects the module's `editOnModal` config:

- `editOnModal = true` → links to the index page with the row ID (opens modal)
- `editOnModal = false` → links directly to the `edit` page

`getNotificationMailRedirector()` instead links to the in-app notification detail route (`admin.system.system_notification.my_notification.show`) so the mail button records a view.

---

## Retainable tray & `retainGroup`

Developer guide (when to use broadcast vs domain events, chat walkthrough): [Feature Notification Broadcasts](/guide/broadcasting/notifications).

Broadcast notifications can opt into a **bottom-right tray** (instead of only the ephemeral top toast stack).

### Opt-in

Override `isRetainable()` (default `false`) and optionally `getRetainGroup()` on a concrete class:

```php
class OrderReadyNotification extends FeatureNotification
{
    public function isRetainable(): bool
    {
        return true;
    }

    public function getRetainGroup(): ?string
    {
        return 'order:'.$this->model->getKey(); // or chat:{id}
    }
}
```

Dismiss from the UI with Vuex `DISMISS_BY_GROUP` / `dismissByGroup('order:123')`. See `ChatableMessageBroadcastNotification` for a built-in consumer.

When retainable, `toBroadcast()` includes:

| Field | Meaning |
|-------|---------|
| `retainable` | `true` |
| `retainUntil` | ISO-8601 expiry (`getRetainHours()`, default 6) |
| `retainGroup` | Stable tray slot key from `getRetainGroup()` (nullable) |

### Tray vs ephemeral toast

| Path | UI | Lifetime |
|------|-----|----------|
| Ephemeral (`retainable: false`) | Top toast stack | Auto-dismiss timeout |
| Retainable (`retainable: true`) | Bottom-right tray | Until Close / Look / TTL / `DISMISS_BY_GROUP` |

### `retainGroup` — one tray slot per logical entity

`getRetainGroup()` defaults to `null`. When set, the frontend **upserts by group** so multiple broadcasts for the same entity replace a single tray item.

Consume on the client with Vuex `DISMISS_BY_GROUP` (or `useRetainableNotifications().dismissByGroup`):

```js
store.commit(RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP, 'chat:42')
```

### `token` ≠ `retainGroup`

| Field | Role |
|-------|------|
| `token` | Unique per notification instance — DB row / mail redirect lookup |
| `retainGroup` | Stable tray key — e.g. one slot per chat |

### Chat example

`ChatableMessageBroadcastNotification` returns `'chat:'.$chat->id`. Opening `Chat.vue` for that chat dismisses the group so the tray clears when the user is already reading the thread.

```php
public function getRetainGroup(): ?string
{
    return 'chat:'.$this->chat->getKey();
}
```

---

## Model Title Resolution

`getModelTitleField()` resolves the model's display title in this order:

1. `createModelTitleField` callback (subclass-specific, then base-class)
2. `$model->notificationTitleField` property
3. `$model->getTitleValue()` method
4. `$model->name` → `$model->title` → `$model->slug` → `$model->id`

Override with a property on your model:

```php
// In your Eloquent model:
public string $notificationTitleField = 'display_name';
```

---

## Outro Lines

Add extra lines at the bottom of the mail body without subclassing:

```php
$notification = new ModelCreatedNotification($model);
$notification->addOutroLine('This is an automated message.');
$user->notify($notification);
```

Or set them as a default on the subclass:

```php
class MyNotification extends FeatureNotification
{
    public array $outroLines = ['Do not reply to this email.'];
}
```

---

## AfterSendable Contract

`Modules\SystemNotification\Notifications\Contracts\AfterSendable`

Notifications that implement this interface receive an `afterNotificationSent($notifiable)` callback after the notification is dispatched. Use it for post-send side effects (e.g. logging, updating a flag).

```php
interface AfterSendable
{
    public function afterNotificationSent($notifiable): void;
}
```

`ChatableUnreadNotification` is the only built-in class that implements `AfterSendable`.

---

## Failure Handling

If a queued notification fails, `failed()` logs the exception to the `modularous-notification-failure` log channel:

```php
public function failed(?\Throwable $exception): void
{
    Log::channel('modularous-notification-failure')->error(
        static::class . ' failed: ' . $exception->getMessage(),
        ['exception' => $exception]
    );
}
```

Configure this channel in `config/logging.php` to route notification failures to a separate file or alerting service.
