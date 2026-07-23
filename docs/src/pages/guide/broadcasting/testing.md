---
sidebarPos: 3
sidebarTitle: Testing
outline: deep
---

# Testing Broadcasts

Broadcast events are **just events** — test them with Laravel's built-in `Event::fake()` / `Event::assertDispatched()` helpers. You don't need a live WebSocket server to verify that the right events fire with the right payload.

## Fake and Assert

```php
use Illuminate\Support\Facades\Event;
use Modules\Order\Events\OrderShipped;

it('fires OrderShipped when status changes to shipped', function () {
    Event::fake([OrderShipped::class]);

    $order = Order::factory()->create(['status' => 'paid']);
    $order->update(['status' => 'shipped']);

    Event::assertDispatched(OrderShipped::class, function ($event) use ($order) {
        return $event->model->is($order);
    });
});
```

`Event::fake([...])` is preferred over the catch-all `Event::fake()` — it only swallows the listed events, letting other listeners (model boot events, observers) keep firing.

## Assert Channels and Payload

Because `broadcastOn()` and `broadcastWith()` are regular methods, you can call them directly in tests:

```php
it('broadcasts on the correct channels', function () {
    $order = Order::factory()->create();
    $event = new OrderShipped($order);

    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(2)
        ->and($channels[0]->name)->toBe('private-models.' . $order->id)
        ->and($channels[1]->name)->toBe('model');
});

it('sends a compact payload', function () {
    $order = Order::factory()->create(['status' => 'shipped']);
    $event = new OrderShipped($order);

    expect($event->broadcastWith())->toMatchArray([
        'id'     => $order->id,
        'status' => 'shipped',
    ]);
});
```

## Testing `broadcastWhen`

```php
it('skips broadcast when status is unchanged', function () {
    $order = Order::factory()->create(['status' => 'paid']);
    $event = new OrderUpdated($order);

    expect($event->broadcastWhen())->toBeFalse();
});

it('broadcasts when status moves to shipped', function () {
    $order = Order::factory()->create(['status' => 'paid']);
    $order->update(['status' => 'shipped']);
    $event = new OrderUpdated($order);

    expect($event->broadcastWhen())->toBeTrue();
});
```

## Testing Trait-Captured Context

Because `ModelEvent` runs `setupEventUser`, `setupEventUrls`, `setupEventChanges`, `setupEventStateable` in its constructor, you can assert against those properties directly.

```php
it('captures the authenticated user and changed attributes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create(['status' => 'paid']);
    $order->update(['status' => 'shipped']);

    $event = new OrderUpdated($order);

    expect($event->user->is($user))->toBeTrue()
        ->and($event->wasChanged('status'))->toBeTrue()
        ->and($event->changedAttributes)->toHaveKey('status', 'shipped');
});
```

## Testing Channel Authorization

Channel authorization lives in `routes/channels.php`. Test it with `$this->actingAs(...)` and the `broadcastingAuth` endpoint, or by invoking the closure directly:

```php
use Illuminate\Support\Facades\Broadcast;

it('authorizes the owning user for their model channel', function () {
    $user  = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/broadcasting/auth', [
            'channel_name' => "private-models.{$order->id}",
            'socket_id'    => '12345.67890',
        ])
        ->assertOk();
});
```

## BroadcastManager Tests

```php
use Unusualify\Modularous\Services\BroadcastManager;

it('builds a broadcast config for a model and event list', function () {
    $order = Order::factory()->create();

    $config = BroadcastManager::forModel($order, [
        OrderCreated::class,
        OrderShipped::class,
    ]);

    expect($config)->toBeArray()
        ->and($config[0])->toHaveKeys(['name', 'type', 'events'])
        ->and($config[0]['events'])->toHaveCount(2);
});
```

## Integration Testing with Reverb

For end-to-end verification against a real Reverb server, run the server on a known port in the test environment and use `pusher-js` (or `laravel-echo`) with a short timeout to subscribe and assert. This is usually overkill — unit-testing the event + manager is enough for CI.

```bash
# In a test-only terminal
REVERB_PORT=9876 php artisan reverb:start --port=9876
```

## Common Patterns

| Want to assert... | Use |
|-------------------|-----|
| An event was dispatched | `Event::assertDispatched(EventClass::class)` |
| With specific data | `Event::assertDispatched(EventClass::class, fn($e) => $e->...)` |
| It was **not** dispatched | `Event::assertNotDispatched(EventClass::class)` |
| Exactly N dispatches | `Event::assertDispatchedTimes(EventClass::class, $n)` |
| Payload shape | Construct the event directly and inspect `broadcastWith()` |
| Channel names | Inspect `broadcastOn()` return value |

## Related

- [Broadcasting Overview](./overview) — setup and default behaviour
- [ModelEvent](/system-reference/backend/events/model-event) — base class and auto-captured context
- [BroadcastManager](/system-reference/backend/services/broadcast-manager) — service under test above
- [Troubleshooting](./troubleshooting) — when tests pass but the browser doesn't receive events
