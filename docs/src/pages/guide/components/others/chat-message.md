---
sidebarPos: 47
sidebarTitle: Chat Message
---
# Chat Message

`ue-chat-message` renders a single chat message bubble with an avatar, sender name, timestamp, message content (with URL linkification), and optional attachment previews. Messages from the current user are displayed right-aligned with `reverse`.

## Usage

```html
<ue-chat-message
  v-model="message"
  update-endpoint="/api/messages/:id"
  :reverse="message.user_id === currentUserId"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Object` | required | Message object (see Message Shape below) |
| `updateEndpoint` | `String` | required | API endpoint called when the user toggles starring or pinning |
| `reverse` | `Boolean` | `false` | Align the bubble to the right (current user's own messages) |
| `avatarSize` | `Number` | `40` | Avatar size in pixels on desktop |
| `mobileAvatarSize` | `Number` | `20` | Avatar size in pixels on mobile |
| `noStarring` | `Boolean` | `false` | Hide the star/unstar icon |
| `noPinning` | `Boolean` | `false` | Hide the pin/unpin icon |
| `contentTruncateLength` | `Number` | `50` | Characters shown before a "Show more" toggle appears |

## Message Shape

```js
{
  id: 1,
  content: 'Hello, world!',
  created_at: '2024-01-15T10:30:00Z',
  is_read: false,
  is_starred: false,
  is_pinned: false,
  user_profile: {
    name: 'Alice',
    avatar_url: '/avatars/alice.jpg',
  },
  attachments: [], // array of filepond file objects
}
```

## Behaviour

- Long messages are truncated to `contentTruncateLength` characters with a "Show more / Show less" toggle.
- URLs in message content are auto-linkified via `ue-well-print`.
- Attachments are rendered with `ue-filepond-preview` using `image-size="24"`.
- Unread messages (where `is_read` is falsy and `reverse` is false) receive a visual highlight.
- Starring and pinning patch the message via `updateEndpoint` and update the `modelValue` optimistically.
