---
sidebarPos: 12
sidebarTitle: make:listener
---

# make:listener

> Create a Laravel Listener class

**Signature**: `modularous:make:listener`

**Category**: Make

---

## Description

Interactive wizard that generates a Laravel Listener class. Scans all concrete event classes from `app/Events`, the vendor path, and every loaded module, then prompts you to bind the listener to one. Supports queued listeners with configurable connection, queue name, delay, tries count, and a `shouldQueue()` method.

---

## Usage

```
modularous:make:listener [options] <name> [<module>]
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Listener class name |
| `module` | no | Target module; omit to create in `app/Listeners/` |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--self` | | Write to Modularous vendor path (`src/Listeners/`) |
| `--force` | `-f` | Overwrite existing file |
| `--should-queue` | | Implement `ShouldQueue`; prompts for connection, queue, delay, tries |
| `--should-handle-events-after-commit` | | Implement `ShouldHandleEventsAfterCommit` |

---

## Examples

### Basic listener for a module

```bash
php artisan modularous:make:listener SendPostPublishedNotification Blog
# Prompts: select the event to listen to
```

### Queued listener

```bash
php artisan modularous:make:listener SendWelcomeEmail --should-queue
# Prompts: event, queue connection, queue name, delay (seconds), tries count
```

### App-level listener

```bash
php artisan modularous:make:listener LogOrderShipped
```

---

## Interactive prompts

1. Select the event class to bind to (or "No" to skip)
2. If `--should-queue`: queue connection, queue name, delay, tries

---

## Output

| Condition | Path |
|-----------|------|
| Module provided | `{Module}/Listeners/{Name}.php` |
| No module | `app/Listeners/{Name}.php` |
| `--self` | `src/Listeners/{Name}.php` (vendor) |

**Stub**: `listener.stub`

---

## See also

- [make:event](./event) — create the matching event
- [System Reference](/guide/console/make/overview)
