---
sidebarPos: 11
sidebarTitle: make:event
---

# make:event

> Create a Laravel Event class

**Signature**: `modularous:make:event`

**Category**: Make

---

## Description

Interactive wizard that generates a Laravel Event class. It scans all abstract event classes found in `app/Events`, the Modularous vendor `src/Events/`, and every loaded module, then lets you choose a base class to extend. Broadcasting options prompt for channel type, queue connection, and channel name.

---

## Usage

```
modularous:make:event [options] <name> [<module>]
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | yes | Event class name |
| `module` | no | Target module; omit to create in `app/Events/` |

### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--self` | | Write to Modularous vendor path (`src/Events/`) |
| `--force` | `-f` | Overwrite existing file |
| `--should-broadcast` | | Implement `ShouldBroadcast`; prompts for channel, queue name |
| `--should-broadcast-now` | | Implement `ShouldBroadcastNow`; prompts for channel |
| `--should-dispatch-after-commit` | | Implement `ShouldDispatchAfterCommit` |

---

## Examples

### Module event

```bash
php artisan modularous:make:event PostPublished Blog
```

### Broadcastable event

```bash
php artisan modularous:make:event PostPublished Blog --should-broadcast
# Prompts: abstract base class, channel type (Channel/Private/Presence), queue, channel name
```

### App-level deferred event

```bash
php artisan modularous:make:event OrderShipped --should-dispatch-after-commit
```

---

## Interactive prompts

1. Select abstract base class (if any exist) — or choose "No"
2. If `--should-broadcast` or `--should-broadcast-now`: select queue connection, enter queue name
3. If broadcasting: select channel type and enter channel name

---

## Output

| Condition | Path |
|-----------|------|
| Module provided | `{Module}/Events/{Name}.php` |
| No module | `app/Events/{Name}.php` |
| `--self` | `src/Events/{Name}.php` (vendor) |

**Stub**: `event.stub`

---

## See also

- [make:listener](./listener) — create the matching listener
- [System Reference](/guide/console/make/overview)
