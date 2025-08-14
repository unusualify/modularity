<template>
  <div
    :class="[
      'd-flex mb-4 w-100',
      reverse ? 'flex-row-reverse' : 'flex-row'
    ]"
    >
    <div
      :style="[
        $vuetify.display.smAndUp ? 'max-width: 60%; min-width: 30%;' : 'max-width: 100%; min-width: 100%;'
      ]"
      :class="[
        'd-flex bg-grey-lighten-6 elevation-2 px-4 py-3 rounded position-relative',
        reverse ? 'flex-row-reverse' : 'flex-row',
        isUnread ? 'v-input-chat__message--unread' : ''
      ]"
      @mouseenter="startReading"
    >
      <!-- Avatar -->
      <v-tooltip :text="formatDate(message)" location="top">
        <template v-slot:activator="{ props }">
          <v-avatar
            :size="$vuetify.display.smAndUp ? avatarSize : mobileAvatarSize"
            :class="[
              reverse ? 'ml-3' : 'mr-3'
            ]"
            :image="message.user_profile.avatar_url" v-bind="props"
          />
        </template>
      </v-tooltip>
      <div
        :stylex="{ width: `calc(50% - ${avatarSize}px)` }"
        class="w-100">
        <!-- Header with name and icons (always clear) -->
        <div
          :class="[
            'text-grey text-caption w-100 d-flex justify-space-between',
            reverse ? 'flex-row-reverse' : 'flex-row'
          ]">

          <div :class="[
            $vuetify.display.smAndUp ? 'w-50' : 'w-75',
            reverse ? 'text-end' : 'text-start'
          ]"
          >
            <div v-if="$vuetify.display.smAndDown" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              {{ formatDate(message) }}
            </div>
            <div>{{ message.user_profile.name }}</div>
          </div>

          <div class="d-flex justify-end">
            <v-icon
              v-if="!noStarring"
              :icon="message.is_starred ? 'mdi-star' : 'mdi-star-outline'"
              :color="message.is_starred ? 'secondary' : 'grey'"
              @click="updateMessage('is_starred', !message.is_starred)"
            />
            <v-icon
              v-if="!noPinning"
              :icon="message.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'"
              :color="message.is_pinned ? 'primary' : 'grey'"
              @click="updateMessage('is_pinned', !message.is_pinned)"
            />
          </div>
        </div>

        <!-- Blurred content wrapper -->
        <div
          :class="[
            'message-content',
            isUnread ? 'message-content--unread' : ''
          ]"
        >
          <!-- Message content -->
          <div :class="['d-flex mt-2 text-break', reverse ? 'flex-row-reverse' : 'flex-row']">
            <div class="w-100" style="color: #32454A; font-weight: 400; font-size: 12px;">
              <template v-if="message.content && message.content.length > contentTruncateLength">
                <div v-if="isExpanded" v-html="formattedContent"></div>
                <div v-else>
                  <span v-html="formattedTruncatedContent"></span>
                  <span class="text-grey-darken-1">...</span>
                </div>

                <v-expand-transition>
                  <div v-if="message.content && message.content.length > contentTruncateLength" class="mt-1">
                    <v-btn
                      :text="isExpanded ? $t('Show less') : $t('Show more')"
                      variant="plain"
                      size="small"
                      color="primary"
                      @click="toggleExpand"
                    />
                  </div>
                </v-expand-transition>
              </template>
              <template v-else>
                <div v-html="formattedContent"></div>
              </template>
            </div>
          </div>

          <!-- Attachments -->
          <div v-if="message.attachments.length > 0" class="mt-2 py-1 rounded">
            <ue-title :text="$t('Attachments')" padding="b-2" type="caption" color="none" transform="none"/>
            <ue-filepond-preview
              :source="message.attachments"
              image-size="24"
              show-file-name
              no-overlay
              show-datex
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      modelValue: {
        type: Object,
        required: true
      },
      avatarSize: {
        type: Number,
        default: 50
      },
      mobileAvatarSize: {
        type: Number,
        default: 25
      },
      reverse: {
        type: Boolean,
        default: false
      },
      updateEndpoint: {
        type: String,
        required: true
      },
      noStarring: {
        type: Boolean,
        default: false
      },
      noPinning: {
        type: Boolean,
        default: false
      },
      contentTruncateLength: {
        type: Number,
        default: 50
      }
    },
    data() {
      return {
        readingTimer: null,
        isExpanded: false
      }
    },
    computed: {
      input: {
        get() {
          return this.modelValue;
        },
        set(value) {
          this.message = value;
          this.$emit('update:modelValue', value);
        }
      },
      message() {
        return this.modelValue;
      },
      isUnread() {
        return !this.message.is_read && !this.reverse;
      },
      truncatedContent() {
        if (this.message.content && this.message.content.length > this.contentTruncateLength) {
          return this.message.content.substring(0, this.contentTruncateLength);
        }
        return this.message?.content ?? '';
      },
      // FormattedContent and formattedTruncatedContent are used to format the content of the message with the new lines.
      formattedContent() {
        return this.message.content ? this.message.content.replace(/\n/g, '<br>') : '';
      },
      formattedTruncatedContent() {
        if (this.message.content.length > this.contentTruncateLength) {
          return this.message.content.substring(0, this.contentTruncateLength).replace(/\n/g, '<br>');
        }
        return this.message?.content?.replace(/\n/g, '<br>') ?? '';
      }
    },
    methods: {
      formatDate(message) {
        let formattedDate = window.$moment().fromNow();

        if(message.created_at) {
          let date = new Date(message.created_at);

          if (Date.now() - date.getTime() < 48 * 60 * 60 * 1000) {
            formattedDate = window.$moment(date).fromNow();
          } else {
            formattedDate = this.$d(new Date(message.created_at), 'numeric-full');
          }
        }

        return formattedDate;
      },
      updateMessage(field, value) {
        let endpoint = this.updateEndpoint.replace(':id', this.input.id);

        let self = this;
        axios.put(endpoint, {
          [field]: value
        }).then(response => {
          self.input = {
            ...self.input,
            [field]: value
          };
        });
      },
      startReading() {
        if (!this.isUnread) return;

        // Wait for transition to complete before marking as read
        this.readingTimer = setTimeout(() => {
          this.markAsRead();
        }, 1000); // Matches the transition duration
      },
      markAsRead() {
        // message.is_read = true;
        this.updateMessage('is_read', true);
      },
      toggleExpand() {
        this.isExpanded = !this.isExpanded;
      }
    },
    beforeUnmount() {
      if (this.readingTimer) {
        clearTimeout(this.readingTimer);
      }
    }
  }
</script>

<style lang="scss">
.message-content--unread {
  filter: blur(2px);
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-input-chat__message--unread {
  opacity: 0.9;
  transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);

  &:hover {
    opacity: 1;
    border-width: 1px;
    border-color: rgba(var(--v-theme-primary), 0.6);
    transform: scale(1.002);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

    .message-content--unread {
      filter: blur(0);
    }
  }
}
</style>
