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
        isUnread ? 'v-input-chat__message--unread' : '',
        editing ? 'v-input-chat__message--editing' : ''
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
      <!-- Edited label positioned at the bottom corner opposite to the time -->
      <span
        v-if="isEdited && $vuetify.display.smAndUp"
        class="text-caption text-grey-darken-1 v-input-chat__message-edited"
        :style="{
          position: 'absolute',
          bottom: '8px',
          fontSize: '10px',
          whiteSpace: 'nowrap',
          zIndex: 1,
          ...(reverse ? { left: '12px', textAlign: 'left' } : { right: '12px', textAlign: 'right' })
        }"
      >
        {{ $t('Edited') }}
      </span>
      <v-tooltip
        v-else
        :text="formattedDateWithEdited"
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
          :text="formattedDateWithEdited"
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

          <div class="d-flex justify-end v-input-chat__message-actions">
            <v-tooltip v-if="canEdit && !editing" :text="$t('Edit')" location="top">
              <template v-slot:activator="{ props: activatorProps }">
                <v-icon
                  v-bind="activatorProps"
                  icon="mdi-pencil-outline"
                  color="grey"
                  @click="$emit('edit', message)"
                />
              </template>
            </v-tooltip>
            <v-tooltip v-if="canDelete && !editing" :text="$t('Delete')" location="top">
              <template v-slot:activator="{ props: activatorProps }">
                <v-icon
                  v-bind="activatorProps"
                  icon="mdi-delete-outline"
                  color="grey"
                  @click="$emit('delete', message)"
                />
              </template>
            </v-tooltip>
            <template v-if="!noStarring">
              <span v-if="updatingFields.is_starred" class="v-input-chat__message-action-loader">
                <v-progress-circular indeterminate size="18" width="2" color="secondary" />
              </span>
              <v-tooltip v-else :text="message.is_starred ? $t('Unstar') : $t('Star')" location="top">
                <template v-slot:activator="{ props: activatorProps }">
                  <v-icon
                    v-bind="activatorProps"
                    :icon="message.is_starred ? 'mdi-star' : 'mdi-star-outline'"
                    :color="message.is_starred ? 'secondary' : 'grey'"
                    @click="updateMessage('is_starred', !message.is_starred)"
                  />
                </template>
              </v-tooltip>
            </template>
            <template v-if="!noPinning">
              <span v-if="updatingFields.is_pinned" class="v-input-chat__message-action-loader">
                <v-progress-circular indeterminate size="18" width="2" color="primary" />
              </span>
              <v-tooltip v-else :text="message.is_pinned ? $t('Unpin') : $t('Pin')" location="top">
                <template v-slot:activator="{ props: activatorProps }">
                  <v-icon
                    v-bind="activatorProps"
                    :icon="message.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'"
                    :color="message.is_pinned ? 'primary' : 'grey'"
                    @click="updateMessage('is_pinned', !message.is_pinned)"
                  />
                </template>
              </v-tooltip>
            </template>
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
  import WellPrint from '@/components/shared/WellPrint.vue'
  import { isObject } from '@/utils/helpers'

  export default {
    components: {
      WellPrint
    },
    emits: ['update:modelValue', 'edit', 'delete'],
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
      // called before pinning, resolves to false to cancel the pin (e.g. the pin limit is reached),
      // or to an object of extra request data, e.g. { pin_duration: 24 }
      beforePin: {
        type: Function,
        default: null
      },
      contentTruncateLength: {
        type: Number,
        default: 50
      },
      // whether this message is currently being edited in the composer
      editing: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        readingTimer: null,
        isExpanded: false,
        now: Date.now(),
        editWindowTimer: null,
        // fields with a pending update request, e.g. { is_pinned: true }
        updatingFields: {}
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
      isEdited() {
        return !!this.message.edited_at;
      },
      canEdit() {
        // only the sender sees the edit action; backend enforces the same rules
        if (!this.reverse || this.message.loading || !this.message.id) return false;
        if (this.isEdited || !this.message.is_editable || !this.message.editable_until) return false;

        return new Date(this.message.editable_until).getTime() > this.now;
      },
      canDelete() {
        // deleting follows the editing rules; backend enforces the same rules
        if (!this.reverse || this.message.loading || !this.message.id) return false;
        if (this.isEdited || !this.message.is_deletable || !this.message.editable_until) return false;

        return new Date(this.message.editable_until).getTime() > this.now;
      },
      formattedDateWithEdited() {
        const date = this.formatDate(this.message);
        return this.isEdited ? `${date} · ${this.$t('Edited')}` : date;
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

      async updateMessage(field, value) {
        if (this.updatingFields[field]) return;

        let extra = {};

        if (field === 'is_pinned' && value && this.beforePin) {
          const result = await this.beforePin(this.input);

          if (!result) return;

          if (isObject(result)) extra = result;
        }

        let endpoint = this.updateEndpoint.replace(':id', this.input.id);

        let self = this;
        this.updatingFields[field] = true;

        axios.put(endpoint, {
          [field]: value,
          ...extra
        }).then(response => {
          self.input = {
            ...self.input,
            [field]: value,
            // the server sets the pin times
            ...(field === 'is_pinned' && isObject(response.data)
              ? { pinned_at: response.data.pinned_at, pinned_until: response.data.pinned_until }
              : {})
          };
        }).finally(() => {
          self.updatingFields[field] = false;
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
      scheduleEditWindowExpiry() {
        if (this.editWindowTimer) {
          clearTimeout(this.editWindowTimer);
          this.editWindowTimer = null;
        }

        if (!this.reverse || !this.message.editable_until) return;

        const remaining = new Date(this.message.editable_until).getTime() - Date.now();

        if (remaining > 0) {
          // re-evaluate canEdit once the edit window closes
          this.editWindowTimer = setTimeout(() => {
            this.now = Date.now();
            this.editWindowTimer = null;
          }, remaining + 100);
        }
      },
    },
    watch: {
      'message.editable_until': {
        handler() {
          this.now = Date.now();
          this.scheduleEditWindowExpiry();
        },
        immediate: true
      }
    },
    beforeUnmount() {
      if (this.readingTimer) {
        clearTimeout(this.readingTimer);
      }
      if (this.editWindowTimer) {
        clearTimeout(this.editWindowTimer);
      }
    }
  }
</script>

<style lang="scss">
.v-input-chat__message-action-loader {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
}

.v-input-chat__message--editing {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18) !important;
  transition: box-shadow 0.2s ease;
}

.v-input-chat__message-edited {
  font-style: italic;
}

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
