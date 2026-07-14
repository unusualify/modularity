<template>
  <div
    :class="[
      'd-flex mb-4 w-100',
      reverse ? 'flex-row-reverse' : 'flex-row'
    ]"
    >
    <div
      :style="[
        $vuetify.display.smAndUp ? 'max-width: 80%; min-width: 50%;' : 'max-width: 100%; min-width: 100%;'
      ]"
      :class="[
        'd-flex elevation-2 px-4 py-3 rounded position-relative',
        reverse ? 'flex-row-reverse bg-primary-lighten-5' : 'flex-row bg-grey-lighten-6',
        isUnread ? 'v-input-chat__message--unread' : ''
      ]"
      @mouseenter="startReading"
    >
      <!-- Time positioned at bottom corner -->
      <span
        v-if="$vuetify.display.smAndUp"
        class="text-caption text-grey-darken-1"
        :style="{
          position: 'absolute',
          bottom: '8px',
          fontSize: '10px',
          whiteSpace: 'nowrap',
          zIndex: 1,
          ...(reverse ? { right: '12px', textAlign: 'right' } : { left: '12px', textAlign: 'left' })
        }"
      >
        {{ formatDate(message) }}
      </span>
      <v-tooltip
        v-else
        :text="formatDate(message)"
        location="top"
      >
        <template v-slot:activator="{ props }">
          <div
            v-bind="props"
            :style="{
              position: 'absolute',
              bottom: '8px',
              ...(reverse ? { right: '8px' } : { left: '8px' })
            }"
          >
          </div>
        </template>
      </v-tooltip>

      <!-- Avatar -->
      <div
        class="d-flex flex-column flex-shrink-0"
        :class="reverse ? 'ml-3 align-end' : 'mr-3 align-start'"
        :style="{ width: `${$vuetify.display.smAndUp ? avatarSize : mobileAvatarSize}px` }"
      >
        <v-tooltip
          v-if="!$vuetify.display.smAndUp"
          :text="formatDate(message)"
          location="top"
        >
          <template v-slot:activator="{ props }">
            <v-avatar
              :size="mobileAvatarSize"
              :image="message.user_profile.avatar_url"
              v-bind="props"
            />
          </template>
        </v-tooltip>
        <v-avatar
          v-else
          :size="avatarSize"
          :image="message.user_profile.avatar_url"
        />
      </div>

      <div
        :stylex="{ width: `calc(50% - ${avatarSize}px)` }"
        class="w-100">
        <!-- Header with name and icons (always clear) -->
        <div
          :class="[
            'text-grey text-caption w-100 d-flex justify-space-between',
            reverse ? 'flex-row-reverse' : 'flex-row'
          ]">

          <div
            v-if="!reverse"
            :class="[
              $vuetify.display.smAndUp ? 'w-50' : 'w-75',
              'text-start'
            ]"
          >
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
          :style="$vuetify.display.smAndUp ? { paddingBottom: '20px' } : {}"
        >
          <!-- Message content -->
          <div :class="['d-flex mt-2 text-break position-relative', reverse ? 'flex-row-reverse' : 'flex-row']">
            <div class="w-100" style="color: #32454A; font-weight: 400; font-size: 12px;">
              <template v-if="message.content && message.content.length > contentTruncateLength">
                <ue-well-print
                  v-if="isExpanded"
                  :text="message.content"
                  :no-linkify="$attrs.noLinkify"
                  class="w-100"
                />
                <div v-else>
                  <ue-well-print
                    :text="truncatedContent"
                    :full-text="message.content"
                    :no-linkify="$attrs.noLinkify"
                    class="w-100"
                  />
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
                <ue-well-print
                  :text="message.content"
                  :no-linkify="$attrs.noLinkify"
                  class="w-100"
                />
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
        default: 40
      },
      mobileAvatarSize: {
        type: Number,
        default: 20
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
      }
    },
    methods: {
      formatDate(message) {
        let formattedDate = window.$moment().fromNow();

        if (message.created_at) {
          let date = new Date(message.created_at);

          if (Date.now() - date.getTime() < 48 * 60 * 60 * 1000) {
            formattedDate = window.$moment(date).locale(this.$i18n.locale).fromNow();

          } else if (Date.now() - date.getTime() < 7 * 24 * 60 * 60 * 1000) {
            formattedDate = window.$moment(date).locale(this.$i18n.locale).format('dddd');

          } else {
            formattedDate = window.$moment(message.created_at).locale(this.$i18n.locale).format('MMM Do YY');
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
      },
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
