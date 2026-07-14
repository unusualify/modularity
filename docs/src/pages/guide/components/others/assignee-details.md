---
sidebarPos: 50
sidebarTitle: Assignee & Assignment
---
# Assignee & Assignment

These two components handle task/assignment UI patterns: viewing assignment details and creating/editing an assignment through a modal form.

## `ue-assignee-details`

Renders a compact list item showing the current assignee's avatar and name. Clicking it opens a popover card with the full assignment summary, description, and attachment list.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `assignment` | `Object` | `{}` | Raw assignment record (due date, description, etc.) |
| `formattedAssignment` | `Object` | `{}` | Pre-formatted version for display (`prependAvatar`, `assigneeName`, `subDescription`) |
| `isAssignee` | `Boolean` | `false` | Whether the current user is the assignee (controls visibility) |
| `isAuthorized` | `Boolean` | `false` | Whether the current user can view/edit the assignment |
| `attachments` | `Array` | `[]` | File attachments linked to the assignment |
| `attachmentsLoading` | `Boolean` | `false` | Show a loading indicator on the attachments section |
| `filepond` | `Object` | `null` | FilePond config for uploading new attachments |

### Events

| Event | Description |
|-------|-------------|
| `update:attachments` | Emitted when attachments change |
| `update:attachmentsLoading` | Emitted when attachment loading state changes |
| `click:complete` | Emitted when the "Mark Complete" action is triggered |
| `click:save` | Emitted when the "Save" action is triggered |

---

## `ue-assignment-modal`

Renders a modal form for creating or editing an assignment. Includes fields for assignee, due date, description, and preliminary tasks.

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Object` | `{}` | Controls modal open state via `v-model` |
| `loading` | `Boolean` | `false` | Show a loading state on submit buttons |
| `form` | `Object` | `{}` | Initial form model (assignee_id, due_at, description, preliminaries) |
| `users` | `Array` | `[]` | List of available assignee options |
| `filepond` | `Object` | `{}` | FilePond config for file attachments |
| `variant` | `String` | `'outlined'` | Vuetify variant for form inputs |
| `disabled` | `Boolean` | `false` | Disable all form inputs |
| `minDueDays` | `Number` | `0` | Minimum number of days in the future for the due date |

### Events

| Event | Description |
|-------|-------------|
| `update:modelValue` | Emitted to close/open the modal |
| `update:form` | Emitted when form fields change |
| `submit` | Emitted on form submission |
