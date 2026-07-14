---
sidebarPos: 26
sidebarTitle: make:feature
---

# make:feature

> Interactive wizard for scaffolding a full Modularous feature bundle

**Signature**: `modularous:make:feature`

**Aliases**: `modularous:create:feature`, `mod:c:feature`

**Category**: Make

---

## Description

`make:feature` is a composite wizard that orchestrates multiple other `make:*` commands to scaffold every layer of a new Modularous feature — from backend traits and models to Vue input components and their tests. Each step is optional; you confirm or skip each component interactively.

---

## Usage

```
modularous:make:feature [<name>]
```

### Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `name` | no | Feature name (prompted if omitted) |

---

## Interactive steps

| Prompt | If yes — calls |
|--------|---------------|
| Create a **repository trait**? | [`make:repository:trait`](./repository-trait) |
| Create a **model trait**? | [`make:model:trait`](./model-trait) |
| Create a **model and migration**? | [`make:model --self --no-defaults`](./model) + [`make:migration --self --no-defaults`](./migration) |
| Create a **Vue input component**? | [`make:vue:input`](./vue-input) |
| → Create a **Vue component test**? | [`make:vue:test`](./vue-test) with `type=component` |
| → Create an **Input Hydrate class**? | [`make:input:hydrate`](./input-hydrate) |

---

## Examples

### Fully interactive

```bash
php artisan modularous:make:feature
# Prompts: feature name, then each component
```

### With name pre-set

```bash
php artisan modularous:make:feature ColorPicker
# Skips the name prompt; still asks about each component
```

---

## Typical session

```
What is the name of the feature? > ColorPicker

Do you want to create a repository trait for this feature? > yes
  → src/Repositories/Traits/ColorPickerTrait.php created

Do you want to create a model trait for this feature? > no

Do you want to create a model and migration for this feature? > no

Do you want to create a vue input component for this feature? > yes
  What will be the name of the input component? > ColorPicker
  → vue/src/js/components/inputs/ColorPicker.vue created

  Do you want to create a vue component test for this input component? > yes
  → test file created

  Do you want to create an input hydrate class for this feature? > yes
  → src/Hydrates/Inputs/ColorPickerHydrate.php created

Feature created successfully
```

---

## Notes

- Model and migration created by this wizard use `--self` (vendor path) and `--no-defaults`.
- This command is `$hidden = true` — it does not appear in `php artisan list`.

---

## See also

- [make:model:trait](./model-trait) — create a trait standalone
- [make:repository:trait](./repository-trait) — create a repository trait standalone
- [make:vue:input](./vue-input) — create a Vue input standalone
- [System Reference](/guide/console/make/overview)
