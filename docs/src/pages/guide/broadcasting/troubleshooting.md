---
sidebarPos: 4
sidebarTitle: Troubleshooting
outline: deep
---

# Broadcasting Troubleshooting

The most common problems and how to diagnose them. Run through in order.

## 1. The Echo Listener Never Fires

**Symptom**: `channel.listen(...)` registered, but the callback never runs even after events dispatch on the server.

### Did you prefix the event name with `.`?

Laravel Echo treats names **without** a leading dot as class strings (`App\Events\Foo`), and names **with** a leading dot as custom broadcast names.

```js
// Wrong — Echo looks for a class, never matches
channel.listen('modularous.model.created', handler)

// Correct — matches broadcastAs() output
channel.listen('.modularous.model.created', handler)
```

### Does the event actually reach Reverb?

Tail the Reverb server log while dispatching:

```bash
php artisan reverb:start --debug
```

You should see a `Broadcasting event` line for every dispatch. If nothing appears:

- The event is missing `implements ShouldBroadcast`.
- `broadcastWhen()` is returning `false`.
- The queue is not running (broadcasts go through the queue when `ShouldBroadcast` is combined with `ShouldQueue` or when the default queue is async). Run `php artisan queue:work`.

### Did you `php artisan config:clear`?

Broadcasting config is cached. After changing `config/broadcasting.php` or `.env`:

```bash
php artisan config:clear
php artisan reverb:restart
```

---

## 2. Private Channel Subscription Fails (403 / `unauthorized`)

**Symptom**: Browser console shows `Echo: error` on a `private-*` channel.

### Register the authorization

Private and presence channels require an entry in `routes/channels.php`:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('models.{modelId}', function ($user, $modelId) {
    return $user !== null; // tighten to real ownership logic
});
```

The channel pattern must match what `broadcastOn()` returns. `new PrivateChannel('models.' . $id)` maps to the `models.{modelId}` pattern.

### Auth endpoint reachable?

The frontend hits `POST /broadcasting/auth` by default. Verify:

- The route is not blocked by CSRF middleware without the correct token (Inertia / Axios default handling usually covers this).
- The session cookie is being sent (check `withCredentials: true` on CORS-bridged setups).
- The user is actually logged in (`Auth::check()` in the channel closure).

### Wrong channel name casing / prefix

Echo auto-prefixes private channels with `private-`. In `channels.php` you register **without** the prefix:

```php
// channels.php
Broadcast::channel('models.{modelId}', /* ... */);  // NOT 'private-models.{modelId}'
```

But when you inspect the channel name from `BroadcastManager::forModel`, you see `private-models.42` — that's correct for the client side.

---

## 3. Events Arrive But Payload Is Empty / Wrong

### Public properties missing

Only **public** properties are serialized into the broadcast payload. A `protected $internalFlag` will not appear.

### `broadcastWith()` returns the wrong shape

If you override `broadcastWith()`, its return value **replaces** the default public-property payload. Remember to include any fields the frontend depends on:

```php
public function broadcastWith(): array
{
    return [
        'id'     => $this->model->id,
        'user'   => $this->user,
        'status' => $this->currentStateableState,
    ];
}
```

### Changes array empty on `ModelCreated`

`getChanges()` returns the changes from the **last save**. On `create`, it is usually empty because the model was freshly inserted rather than updated. Use the model itself, not `changedAttributes`, for create events.

---

## 4. Events Dispatch Twice (Or More)

- Check for **duplicate observers** — if the event is dispatched both in the model's `boot()` and a repository trait, it will fire twice.
- `queue:work` running multiple processes on the same queue connection can re-dispatch retried jobs. Use idempotent listeners or `ShouldBeUnique`.
- Hot-reload tooling (Vite HMR) can re-instantiate Echo on every save, so the **subscriber** side doubles up. In dev, ensure `Echo.leave(...)` is called in Vue `onBeforeUnmount`.

---

## 5. Reverb Connection Refused / TLS Errors

### Local dev

```dotenv
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Echo config must match the scheme:

```js
new Echo({
    broadcaster:      'reverb',
    forceTLS:         import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
})
```

### Behind a reverse proxy (nginx / Caddy)

Add WebSocket upgrade headers:

```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_read_timeout 86400;
}
```

### Production

- Run `reverb:start` under Supervisor / systemd so it restarts on crash.
- Terminate TLS at the proxy; run Reverb on a local HTTP port.
- Set `REVERB_SCHEME=https` so Echo uses `wss`.

---

## 6. `BroadcastManager::forModel` Returns an Empty Array

Each event class passed to `forModel` must:

1. Accept `$model` as the first constructor arg.
2. Implement `broadcastOn()` returning `Channel[]`.
3. Optionally implement `broadcastAs()`.

If one of the event classes errors in its constructor, the whole call can return early. Wrap the call in try/catch during debugging to surface the exception.

```php
try {
    $config = BroadcastManager::forModel($model, $eventClasses);
} catch (\Throwable $e) {
    logger()->error('BroadcastManager failed', [
        'model' => $model->getKey(),
        'error' => $e->getMessage(),
    ]);
    $config = [];
}
```

---

## 7. Stateable Context Not Populated

`$event->hasStateable` is `false` when the model does not use the `HasStateable` trait, or when the trait's state query fails silently. Verify:

```php
use function Unusualify\Modularous\classHasTrait;
use Unusualify\Modularous\Entities\Traits\HasStateable;

classHasTrait($order, HasStateable::class); // must be true
```

See [ModelEvent / EventStateable](/system-reference/backend/events/traits/event-stateable).

---

## Quick Diagnostic Checklist

| Check | Command / Action |
|-------|------------------|
| Reverb running | `php artisan reverb:start --debug` |
| Queue running | `php artisan queue:work` |
| Config fresh | `php artisan config:clear` |
| Event implements `ShouldBroadcast` | Class check |
| Echo event name has leading `.` | Client code |
| Channel auth registered | `routes/channels.php` |
| `forceTLS` matches scheme | Echo config vs `REVERB_SCHEME` |
| User authenticated | `Auth::check()` for private channels |

## Related

- [Broadcasting Overview](./overview) — setup and default behaviour
- [ModelEvent](/system-reference/backend/events/model-event) — base class reference
- [Testing](./testing) — assert events fire correctly
