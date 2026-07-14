---
sidebarPos: 14
sidebarTitle: make:horizon:supervisor
---

# make:horizon:supervisor

> Generate a Supervisor configuration file for Laravel Horizon

**Signature**: `modularous:make:horizon:supervisor`

**Alias**: `modularous:create:horizon:supervisor`

**Category**: Make

---

## Description

Interactive wizard that creates a Supervisor `.conf` file for running Laravel Horizon as a daemon. Automatically detects the OS (macOS or Linux), locates a writable Supervisor config directory, and checks for existing processes with the same app name before writing. Prints the `supervisorctl` commands needed to activate the new config.

---

## Usage

```
modularous:make:horizon:supervisor
```

No arguments or options — fully interactive.

---

## Interactive prompts

| Prompt | Default | Description |
|--------|---------|-------------|
| Config name | `b2press-app` | Used as the supervisor program name prefix |
| PHP binary | `php` | Path to the PHP executable |
| App path | `base_path()` | Absolute path to your Laravel application |
| Command | `artisan horizon` | Artisan command to run (prepended with app path) |
| User | `root` | OS user to run the process as |
| Log file name | `horizon` | Base name for the log file |

---

## After running

The command prints the three activation commands:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start {programName}
```

---

## Supervisor config directories checked

**macOS** (Homebrew):
- `/usr/local/etc/supervisor/conf.d`
- `/usr/local/etc/supervisor.d`
- `/opt/homebrew/etc/supervisor.d`

**Linux**:
- `/etc/supervisor/conf.d`
- `/etc/supervisord.d`
- `/etc/supervisord/conf.d`

---

## Notes

- The command will suggest install instructions if Supervisor is not found (`brew install supervisor` or `apt-get install supervisor`).
- Not supported on Windows.
- The generated program name is unique (`{appName}-{uniqid()}-horizon`) to avoid conflicts.

---

## See also

- [System Reference](/guide/console/make/overview)
