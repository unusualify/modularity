<template>
  <div>
    <!-- EMOJI PICKER DIALOG -->
    <v-dialog
      v-model="showEmojiPicker"
      width="400"
      max-width="90vw"
    >
      <template v-slot:default>
        <v-card>
          <v-card-title class="d-flex justify-space-between align-center">
            {{ $t('Select Emoji') }}
            <v-btn
              icon="mdi-close"
              variant="text"
              size="small"
              @click="closeEmojiPicker"
            />
          </v-card-title>
          <v-card-text>
            <div class="emoji-grid">
              <v-btn
                v-for="emoji in emojiList"
                :key="emoji"
                variant="text"
                size="small"
                class="emoji-btn"
                @click="selectEmoji(emoji)"
              >
                {{ emoji }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </template>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'v-input-emojis',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'emoji-selected'],
  data() {
    return {
      emojiList: [
        '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇',
        '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚',
        '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩',
        '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣',
        '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬',
        '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗',
        '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😯', '😦', '😧',
        '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢',
        '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '💩', '🤡', '👹',
        '👺', '👻', '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼',
        '😽', '🙀', '😿', '😾', '🙈', '🙉', '🙊', '💌', '💘', '💝',
        '💖', '💗', '💓', '💞', '💕', '💟', '❣️', '💔', '❤️', '🧡',
        '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💯', '💢', '💥',
        '💫', '💦', '💨', '🕳️', '💬', '🗨️', '🗯️', '💭', '💤', '👋',
        '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟',
        '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎',
        '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏',
        '✍️', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠',
        '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '💋', '🩸'
      ]
    }
  },
  computed: {
    showEmojiPicker: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      }
    }
  },
  methods: {
    openEmojiPicker() {
      this.showEmojiPicker = true;
      // console.log('Emoji picker opened:', this.showEmojiPicker);
    },

    closeEmojiPicker() {
      this.showEmojiPicker = false;
      // console.log('Emoji picker closed:', this.showEmojiPicker);

    },

    selectEmoji(emoji) {
      this.$emit('emoji-selected', emoji);
      this.closeEmojiPicker();
    }
  }
}
</script>

<style lang="scss">
.emoji-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 8px;
  max-height: 300px;
  overflow-y: auto;
  padding: 8px;
}

.emoji-btn {
  font-size: 24px;
  min-width: 40px;
  height: 40px;
  border-radius: 8px;
  transition: all 0.2s ease;

  &:hover {
    background-color: rgba(var(--v-theme-primary), 0.1);
    transform: scale(1.1);
  }
}
</style>
