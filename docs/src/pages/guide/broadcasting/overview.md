---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Broadcasting
outline: deep
---

# Broadcasting

Modularous uses **Laravel Reverb** (or any Pusher-compatible driver) for realtime updates. There are two complementary paths:

1. **Domain events** — `ShouldBroadcast` classes (often `ModelEvent` subclasses, or focused events like `ChatableMessageSynced`) that fan out on public/private channels for live UI sync.
2. **User notifications** — `FeatureNotification` subclasses with the `broadcast` channel, delivered to `private-users.{id}` and consumed as ephemeral toasts or a retainable tray.

Both paths are gated by `BroadcastAvailability` / `modularous.broadcasting.enabled` (and the Pusher PHP SDK when using `reverb` / `pusher`).

## In This Section

| Page | Purpose |
|------|---------|
| **Overview** (this page) | When to use what, ModelEvent channels, Echo setup, ops checklist |
| [Feature Notifications](./notifications) | `FeatureNotification` broadcast payload, retainable tray, chat end-to-end |
| [Testing](./testing) | `Event::fake()` patterns, asserting channels and payloads |
| [Troubleshooting](./troubleshooting) | Common issues (`.` prefix, auth failures, duplicate dispatches, proxies) |

Class references live under `system-reference/backend/`:

- [ModelEvent](/system-reference/backend/events/model-event) — base class + `EventUser`, `EventUrls`, `EventChanges`, `EventStateable` traits
- [BroadcastManager](/system-reference/backend/services/broadcast-manager) — build frontend channel/event config from PHP
- [FeatureNotification](/system-reference/backend/notifications/feature-notification) — full contract, retainable tray + `retainGroup`
- [Chatable](/system-reference/backend/entity-traits/relationships/chatable) · [ChatableScheduler](/system-reference/backend/scheduled-jobs/chatable-scheduler)

---

## When to Use What

Pick the path that matches the audience and UI job:

| Goal | Mechanism | Typical channel | UI |
|------|-----------|-----------------|----|
| Live sync for anyone looking at a record / chat | Domain `ShouldBroadcast` event (+ `GatesBroadcastAvailability` when not extending `ModelEvent`) | e.g. `private-chats.{id}`, `private-models.{id}`, public `model` | Update local state (list, badges) — **not** a toast |
| Alert a specific user (toast / tray / inbox) | `FeatureNotification` with `broadcast` in `via()` | Laravel → `private-users.{id}` via `BroadcastNotificationCreated` | Echo `channel.notification` → toast or retainable tray |
| Domain fan-out (optional listeners / dashboards) | Public channel event | e.g. `unread-chat-message` | Optional; chat **toasts still go through** user notifications |

**Rules of thumb**

- **Same-screen collaboration** (open chat thread, concurrent editors) → domain private channel + payload your Vue component already understands.
- **“Something happened while you were elsewhere”** → `FeatureNotification` → user private channel → toast/tray (+ `database` / `mail` as needed).
- **Do not** put toast copy on a domain event and hope the admin shell picks it up — user toasts are wired to `users.{id}` notification broadcasts (see [Feature Notifications](./notifications)).

### Domain event vs user notification (chat)

```
Message created
    ├── ChatableMessageSynced  →  private chats.{id}     →  open Chat.vue list sync
    ├── ChatableMessageBroadcastNotification (retainable)
    │                         →  users.{recipientId}     →  bottom-right tray
    └── (later, scheduler) ChatableUnreadNotification (ephemeral)
                              →  users.{recipientId}     →  top toast + mail/DB
```

Full walkthrough: [Chat as an end-to-end example](./notifications#chat-as-an-end-to-end-example).

---

## Retainable notifications (tray)

Feature notifications may opt into a **retainable** bottom-right tray via `isRetainable()`. Ephemeral toasts stay in the top stack; retainable ones persist until dismiss, TTL, or a **group dismiss**.

Use `retainGroup` (not `token`) as the stable tray slot key — e.g. chat uses `chat:{id}` and `Chat.vue` commits `DISMISS_BY_GROUP` when that chat opens.

```php
use Modules\SystemNotification\Notifications\FeatureNotification;

class OrderReadyNotification extends FeatureNotification
{
    public function isRetainable(): bool
    {
        return true;
    }

    /** One tray slot per order — later broadcasts upsert the same item. */
    public function getRetainGroup(): ?string
    {
        return 'order:'.$this->model->getKey();
    }
}
```

Dismiss that slot from the UI with Vuex `DISMISS_BY_GROUP` / `dismissByGroup('order:123')`. Real consumer: `ChatableMessageBroadcastNotification` (`chat:{id}`).

→ Full contract & payload fields: [Feature Notifications guide](./notifications) · [FeatureNotification reference](/system-reference/backend/notifications/feature-notification#retainable-tray--retaingrouproup)

---

## How It Works

When a `ModelEvent` subclass that uses `InteractsWithBroadcasting` is dispatched, the constructor calls `$this->broadcastVia($this->broadcastService)` (defaults to `'reverb'`). Laravel then serializes and delivers the event payload over WebSockets.

Events that do **not** extend `ModelEvent` should use the `GatesBroadcastAvailability` trait so `broadcastWhen()` respects `BroadcastAvailability::isEnabled()`.

### Default Channels

Every `ModelEvent` broadcasts on two channels:

| Channel | Type | Pattern | Purpose |
|---------|------|---------|---------|
| `models.{model_id}` | Private | One channel per model instance | Per-record real-time updates |
| `model` | Public | Single shared channel | All model updates across the app |

### Event Name Convention

The broadcast event name is derived from the class name:

```
ClassName            →  broadcast event name
────────────────────────────────────────────
OrderShippedEvent    →  modularous.order.shipped
ModelCreated         →  modularous.model.created
StateableUpdated     →  modularous.stateable.updated
```

The rule: strip `_event` suffix, convert to snake_case, replace `_` with `.`, prepend `modularous.`.

---

## Server Configuration

### 1. Install and Configure Reverb

```bash
php artisan reverb:install
```

In `config/broadcasting.php`, ensure the `reverb` connection is configured:

```php
'reverb' => [
    'driver'  => 'reverb',
    'key'     => env('REVERB_APP_KEY'),
    'secret'  => env('REVERB_APP_SECRET'),
    'app_id'  => env('REVERB_APP_ID'),
    'options' => [
        'host'   => env('REVERB_HOST'),
        'port'   => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
    ],
    'timeout' => null,
],
```

Set the default driver to `reverb` (or switch per-event):

```php
// config/broadcasting.php
'default' => env('BROADCAST_DRIVER', 'reverb'),
```

### 2. Start the Reverb Server

```bash
php artisan reverb:start
```

For production, use a process manager (Supervisor, systemd) to keep the server running.

---

## Creating a Broadcast Event

To make a `ModelEvent` subclass broadcast, add `ShouldBroadcast` and `InteractsWithBroadcasting`:

```php
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Unusualify\Modularous\Events\ModelEvent;

class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;
}
```

That's all — `ModelEvent` provides `broadcastOn()` and `broadcastAs()` automatically.

### Standalone events (non-ModelEvent)

```php
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Unusualify\Modularous\Events\Traits\GatesBroadcastAvailability;

class ChatableMessageSynced implements ShouldBroadcast
{
    use GatesBroadcastAvailability;

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chats.' . $this->chatId)];
    }

    public function broadcastAs(): string
    {
        return 'modularous.chatable.message.synced';
    }
}
```

Register authorization for any new private channel in package/app `routes/channels.php` (Modularous already registers `users.{id}`, `models.{id}`, `chats.{id}`, `editing.{modelType}.{modelId}`).

### Overriding Channels

To broadcast on custom channels instead of the defaults:

```php
use Illuminate\Broadcasting\PrivateChannel;

class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->model->id),
            new PrivateChannel('users.' . $this->model->user_id),
        ];
    }
}
```

### Overriding the Broadcast Driver

To use a different driver for a specific event:

```php
class OrderShipped extends ModelEvent implements ShouldBroadcast
{
    use InteractsWithBroadcasting;

    public string $broadcastService = 'pusher'; // override from 'reverb'
}
```

---

## BroadcastManager — Building Frontend Config

`BroadcastManager::forModel()` inspects a list of event classes for a given model and returns a structured config array that can be passed to the frontend so Laravel Echo can subscribe dynamically.

```php
use Unusualify\Modularous\Services\BroadcastManager;
use Modules\SystemNotification\Events\ModelCreated;
use Modules\SystemNotification\Events\ModelUpdated;
use Modules\SystemNotification\Events\StateableUpdated;

$broadcastConfig = BroadcastManager::forModel($order, [
    ModelCreated::class,
    ModelUpdated::class,
    StateableUpdated::class,
]);
```

The returned array has the shape:

```php
[
    [
        'name'   => 'private-orders.42',
        'type'   => 'private',
        'events' => [
            ['event' => 'modularous.model.created'],
            ['event' => 'modularous.model.updated'],
            ['event' => 'modularous.stateable.updated'],
        ],
    ],
    [
        'name'   => 'model',
        'type'   => 'public',
        'events' => [/* same events */],
    ],
]
```

Pass this to the frontend via Inertia shared data or a Blade variable:

```php
// In your controller or middleware:
Inertia::share('broadcastConfig', BroadcastManager::forModel($model, $eventClasses));
```

→ [BroadcastManager service reference](/system-reference/backend/services/broadcast-manager)

---

## Frontend — Subscribing with Laravel Echo

The Modularous admin shell already boots Echo from `vue/src/js/plugins/broadcasting.js` when `STORE.broadcast.enabled` is not `false` and `VITE_REVERB_APP_KEY` is set. It:

- Subscribes to `private-users.{userId}` and handles `channel.notification` (toasts / retainable tray)
- Optionally listens on public domain channels (`model`, `stateable`, …)

For **feature-specific** live sync (e.g. an open chat), subscribe in the component — see [Feature Notifications → Chat](./notifications#live-list-chatablemessagesynced).

### Manual Echo setup (custom frontends)

```bash
npm install laravel-echo pusher-js
```

```js
// resources/js/bootstrap.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster:      'reverb',
    key:              import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:           import.meta.env.VITE_REVERB_HOST,
    wsPort:           import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort:          import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:         (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
})
```

### Subscribe Using BroadcastManager Config

```js
function subscribeToBroadcast(broadcastConfig) {
    broadcastConfig.forEach(({ name, type, events }) => {
        const channel = type === 'private'
            ? Echo.private(name)
            : Echo.channel(name)

        events.forEach(({ event }) => {
            channel.listen(`.${event}`, (payload) => {
                console.log(`Event ${event} received:`, payload)
                // update your Vue store / reactive state here
            })
        })
    })
}

// In a Vue component (using Inertia shared data):
const { broadcastConfig } = usePage().props
subscribeToBroadcast(broadcastConfig)
```

::: tip Event name prefix
Laravel Echo requires a leading `.` before custom event names: `.modularous.model.created`. Without it, Echo treats the name as a Laravel event class string.
:::

### Authorizing Private Channels

Private channels require `Broadcast::channel()` authorization. Modularous registers core channels with the **Modularous auth guard** (not `web` alone) — see package `routes/channels.php`.

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('models.{modelId}', function ($user, $modelId) {
    return $user !== null; // adjust to your auth logic
});
```

---

## Ops Checklist

Short path to a working broadcast stack in local / staging:

1. **Reverb** — `php artisan reverb:start` (or process manager in production). Match `REVERB_*` and `VITE_REVERB_*`.
2. **Queue / Horizon** — `FeatureNotification` implements `ShouldQueue`. Broadcast delivery uses `BroadcastNotificationCreated` on the notification `broadcast` connection (`modularous.notifications.broadcast_connection`). Run Horizon / `queue:work` for that connection.
3. **Chatable interval** — schedule `modularous:scheduler:chatable` (see [ChatableScheduler](/system-reference/backend/scheduled-jobs/chatable-scheduler)) for delayed unread reminders.
4. **Frontend gate** — admin Echo only starts when `STORE.broadcast.enabled !== false` and a Reverb key is present. Ensure bootstrap shares `broadcast.userId` (or user profile id) so `users.{id}` can subscribe.
5. **Config cache** — after `.env` / broadcasting changes: `php artisan config:clear` (and restart Reverb / Horizon).

Master toggle: `MODULAROUS_BROADCAST_ENABLED` → `modularous.broadcasting.enabled`. When false (or Pusher SDK missing for reverb/pusher), domain `broadcastWhen()` skips and FeatureNotification strips the `broadcast` channel.

More failure modes: [Troubleshooting](./troubleshooting).

---

## Environment Variables

```dotenv
BROADCAST_DRIVER=reverb
MODULAROUS_BROADCAST_ENABLED=true

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Frontend (Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```
