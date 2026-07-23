---
sidebarPos: 2
sidebarTitle: Feature Notifications
outline: deep
---

# Feature Notification Broadcasts

How to ship **user-targeted** realtime alerts with `FeatureNotification`: channels, `toBroadcast` payload, ephemeral vs retainable UI, and the chat stack as a complete reference implementation.

For domain `ShouldBroadcast` events, Echo setup, and ops, see [Broadcasting Overview](./overview). Full class contract: [FeatureNotification](/system-reference/backend/notifications/feature-notification).

---

## Mental Model

```
$user->notify(new MyFeatureNotification($model))
        │
        ├─ via() → e.g. database, mail, broadcast
        │            (broadcast stripped if BroadcastAvailability is off)
        │
        └─ broadcast channel
               → Illuminate BroadcastNotificationCreated
               → private users.{notifiableId}
               → Echo channel.notification(...)
               → pushBroadcastToastFromPayload(...)
                     ├─ retainable: true  → retainableNotification store (tray)
                     └─ else              → broadcastToast store (top stack)
```

Domain events (public/private channels for live sync) are a **separate** path — they do not replace this flow for toasts.

---

## Channels (`via` / config / empty-env fallback)

`FeatureNotification::via()` delegates to `getClassChannels()`:

1. Read `config("modularous.notifications.{FQCN}.channels")` (comma-separated string from env merges).
2. If the value is **missing, empty, or whitespace-only**, use the subclass `$defaultChannels` (blank env must not wipe channels).
3. Filter to `$validChannels`.
4. If `BroadcastAvailability::isEnabled()` is false, **remove** `broadcast`.

Package defaults live in `config/merges/notifications.php`, e.g.:

```php
'Modules\SystemNotification\Notifications\ChatableMessageBroadcastNotification' => [
    'channels' => env('MODULAROUS_NOTIFICATIONS_CHATABLE_MESSAGE_BROADCAST_CHANNELS')
        ?: 'database,broadcast',
],
'Modules\SystemNotification\Notifications\ChatableUnreadNotification' => [
    'channels' => env('MODULAROUS_NOTIFICATIONS_CHATABLE_UNREAD_CHANNELS')
        ?: 'database,mail,broadcast',
],
```

In a concrete notification, set a safe fallback:

```php
class OrderReadyNotification extends FeatureNotification
{
    public $defaultChannels = ['database', 'broadcast'];
}
```

Queue connections: `viaConnections()` maps `broadcast` → `modularous.notifications.broadcast_connection` (Horizon must process that connection for `BroadcastNotificationCreated`).

---

## `toBroadcast` payload

`toBroadcast()` builds on `toDatabaseFeatureFields()` then adjusts the redirector and retainable flags:

| Field | Role |
|-------|------|
| `token` | Unique per notification instance (DB / mail redirect lookup) |
| `subject` / `message` / `htmlMessage` | Toast / tray copy |
| `redirectorText` / `redirector` / `hasRedirector` | CTA |
| `notification_type` | `class_basename` of the notification |
| `retainable` / `retainUntil` / `retainGroup` | Tray opt-in (see below) |

### Redirector preference

Same pattern as mail:

1. Prefer `getNotificationMailRedirector()` → `admin.system.system_notification.my_notification.show` when a **database** row exists for this `token` (marks read, then redirects).
2. Fall back to the direct model redirector from `getNotificationRedirector()` when the DB row is not available yet (broadcast-only channel, or race if `database_connection` is not sync).

Default `database_connection` in merges is `sync` so the show URL is usually available when broadcast runs.

---

## Ephemeral toast vs retainable tray

| | Ephemeral | Retainable |
|---|-----------|------------|
| Opt-in | default (`isRetainable()` → `false`) | override `isRetainable()` → `true` |
| UI | Top toast stack (`BroadcastToasts`) | Bottom-right tray (`RetainableNotifications`) |
| Lifetime | Auto-dismiss timeout | Until Close / Look / TTL / `DISMISS_BY_GROUP` |
| Payload | `retainable: false` | `retainable`, `retainUntil` (ISO-8601 from `getRetainHours()`, default 6h), `retainGroup` |

### Example: retainable order alert

```php
use Modules\SystemNotification\Notifications\FeatureNotification;

class OrderReadyNotification extends FeatureNotification
{
    public $defaultChannels = ['database', 'broadcast'];

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

### `token` ≠ `retainGroup`

| Field | Role |
|-------|------|
| `token` | Unique instance id for DB/mail |
| `retainGroup` | Stable tray key (one slot per logical entity, e.g. `order:123`, `chat:42`) |

When `retainGroup` is set, the frontend **upserts by group** so repeated broadcasts replace a single tray item.

→ Reference detail: [FeatureNotification → Retainable tray](/system-reference/backend/notifications/feature-notification#retainable-tray--retaingrouproup)

---

## How the UI consumes broadcasts

Wired in `vue/src/js/plugins/broadcasting.js` (admin shell):

1. Resolve `userId` from `STORE.broadcast.userId` (fallback: user profile id).
2. `Echo.private('users.' + userId)`.
3. `channel.notification(handler)` maps subject/message/retainable fields via `pushBroadcastToastFromPayload`.
4. `listenToAll` fallback for structured `toast` payloads or Laravel notification shapes if Echo’s notification binding misses.

### Retainable store API

```js
import useRetainableNotifications from '@/hooks/useRetainableNotifications'
import { RETAINABLE_NOTIFICATION } from '@/store/mutations'

const { dismissByGroup } = useRetainableNotifications()

// Clear one logical slot when the user opens the related UI
dismissByGroup('order:123')
// or
store.commit(RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP, 'chat:42')
```

Tray items hydrate from `localStorage` per user (`modularous.retainableNotifications.{userId}`) and prune on TTL.

Ephemeral path commits `BROADCAST_TOAST.PUSH` instead.

---

## Chat as an end-to-end example

Chat uses **all three** broadcast roles. Use it as a template when designing a feature with live collaboration + alerts.

### Immediate: `ChatableMessageBroadcastNotification`

- Fired from `ChatController` → `Chatable::notifyChatableMessageBroadcast()` on message **create**.
- Channels default: `database,broadcast` (no mail).
- **Retainable** with `retainGroup = 'chat:{chatId}'`.
- Does **not** stamp `notified_at` — interval unread path stays independent.

### Interval unread: `ChatableUnreadNotification`

- Fired from `Chatable::handleChatableNotification()` when the latest message is unread, past the interval, and `notified_at` is null.
- Scheduler: `modularous:scheduler:chatable` ([ChatableScheduler](/system-reference/backend/scheduled-jobs/chatable-scheduler)).
- Channels default: `database,mail,broadcast`.
- **Ephemeral** toast (not retainable).
- Also dispatches domain event `UnreadChatMessage` on public `unread-chat-message` (fan-out / listeners — **not** the user toast).
- Stamps `notified_at` so the same message is not re-notified forever.

### Live list: `ChatableMessageSynced`

- Domain event on `private-chats.{chatId}`, `broadcastAs`: `modularous.chatable.message.synced`.
- Uses `GatesBroadcastAvailability`.
- Payload: `{ action: created|updated|deleted, chat_id, message }`.
- Dispatched from `ChatController` on create / update / destroy.
- `Chat.vue` listens with a leading `.` and mutates the open thread — **no toast**.

```js
Echo.private(`chats.${chatId}`)
  .listen('.modularous.chatable.message.synced', (payload) => {
    // merge / remove message in local list
  })
```

### Opening Chat dismisses the retain group

When the user opens a chat whose id is `input`, `Chat.vue` commits:

```js
store.commit(RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP, `chat:${chatId}`)
```

So the tray clears for that thread once they are already reading it.

### Decision map (chat)

| Concern | Class | Channel | UI |
|---------|-------|---------|-----|
| Sync open thread | `ChatableMessageSynced` | `chats.{id}` | In-component list |
| “New message” while elsewhere | `ChatableMessageBroadcastNotification` | `users.{id}` | Retainable tray |
| Reminder after interval | `ChatableUnreadNotification` | `users.{id}` (+ mail/DB) | Ephemeral toast |
| Domain signal | `UnreadChatMessage` | public `unread-chat-message` | Optional listeners |

Entity API: [Chatable trait](/system-reference/backend/entity-traits/relationships/chatable).

---

## Checklist for a new feature notification

1. Extend `FeatureNotification`; set `$defaultChannels` (include `broadcast` only if you want realtime).
2. Add a `config/merges/notifications.php` (or app override) entry with `env(...) ?: 'database,broadcast'`.
3. Decide ephemeral vs retainable; if retainable, implement `getRetainGroup()` and dismiss that group from the relevant Vue screen.
4. Rely on `toBroadcast` defaults or override subject/message via callbacks / subclass methods.
5. Confirm Horizon processes the broadcast notification connection; confirm Echo has `STORE.broadcast.userId`.
6. Keep live collaboration on a dedicated `ShouldBroadcast` channel if the open UI needs structured sync — don’t overload the toast payload for that.

---

## See also

- [Broadcasting Overview](./overview) — when to use what, ModelEvent, ops checklist
- [FeatureNotification](/system-reference/backend/notifications/feature-notification) — callbacks, database payload, AfterSendable
- [System notifications](/system-reference/backend/notifications/system-notifications) — built-in notification catalogue
- [Testing broadcasts](./testing) · [Troubleshooting](./troubleshooting)
