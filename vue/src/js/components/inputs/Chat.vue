<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="v-input-chat"
    >
    <template v-slot:default="defaultSlot">
      <v-card
        class='w-100'
        :variant="variant"
        :elevation="elevation"
        :class="[
          !noBackground && background ? `bg-${background}` : '',
          disabled ? 'bg-grey-lighten-4' : '',
          editingMessage ? 'v-input-chat--editing' : ''
        ]"
        :color="color"
        :heightx="height"
        :disabled="disabled"
      >
        <v-card-title class="w-100 py-4 text-wrap v-input-chat__dimmable" @click="cancelEditing">
          <ue-title :text="label" align="center" padding="a-0" transform="none" class="w-100" type="body-1" color="grey-darken-4" weight="medium">
            <template v-slot:right>
              <!-- STARRED MESSAGES -->
              <div v-if="hasStarredMessages" class="ml-auto pl-2 flex-shrink-0">
                <v-input-browser
                  ref="starredBrowser"
                  load-on-open
                  no-selection
                  load-more
                  :endpoint="endpoints.starredMessages.replace(':id', input)"
                  item-title="content"
                  item-value="id"
                  @click:item="jumpToStarredMessage"
                >
                  <template v-slot:activator="{ props: browserProps }">
                    <v-tooltip :text="$t('Starred messages')" location="top">
                      <template v-slot:activator="{ props: activatorProps }">
                        <v-btn
                          v-bind="activatorProps"
                          variant="text"
                          color="secondary"
                          density="comfortable"
                          size="small"
                          icon
                          @click.stop="browserProps.onClick"
                        >
                          <v-badge
                            :model-value="starredCount > 0"
                            :content="starredCount > 99 ? '99+' : starredCount"
                            color="secondary"
                            floating
                          >
                            <v-icon>{{ starredCount > 0 ? 'mdi-star' : 'mdi-star-outline' }}</v-icon>
                          </v-badge>
                        </v-btn>
                      </template>
                    </v-tooltip>
                  </template>
                  <template v-slot:item="{ item }">
                    <div class="d-flex align-start">
                      <div class="flex-grow-1 overflow-hidden">
                        <v-list-item-title v-if="item.raw.content">{{ item.raw.content }}</v-list-item-title>
                        <!-- clicking a file opens it instead of jumping to the message -->
                        <div v-if="pinnedMessageAttachments(item.raw).length" class="py-1" @click.stop>
                          <ue-filepond-preview
                            :source="pinnedMessageAttachments(item.raw)"
                            image-size="24"
                            show-file-name
                            no-overlay
                          />
                        </div>
                        <v-list-item-subtitle>{{ starredMessageSender(item.raw) }}</v-list-item-subtitle>
                      </div>
                      <div class="d-flex flex-column align-end flex-shrink-0 pl-4">
                        <span class="text-caption text-medium-emphasis">{{ starredMessageDate(item.raw) }}</span>
                        <span v-if="item.raw.edited_at" class="text-caption text-grey-darken-1 font-italic">{{ $t('Edited') }}</span>
                      </div>
                    </div>
                  </template>
                </v-input-browser>
              </div>
              <!-- UNSTAR ALL -->
              <div v-if="canUnstarAll" class="flex-shrink-0">
                <v-tooltip :text="$t('Unstar All')" location="top">
                  <template v-slot:activator="{ props: activatorProps }">
                    <v-btn
                      v-bind="activatorProps"
                      variant="text"
                      color="secondary"
                      density="comfortable"
                      size="small"
                      icon="mdi-star-off-outline"
                      :loading="unstarAllLoading"
                      @click.stop="unstarAllDialog = true"
                    />
                  </template>
                </v-tooltip>
              </div>
              <!-- MARK ALL AS READ -->
              <div v-if="canMarkAllAsRead" :class="[hasStarredMessages ? '' : 'ml-auto', 'pl-2 flex-shrink-0']">
                <v-tooltip :text="$t('Mark All as Read')" location="top" :disabled="$vuetify.display.smAndUp">
                  <template v-slot:activator="{ props: activatorProps }">
                    <v-btn
                      v-bind="activatorProps"
                      variant="text"
                      color="primary"
                      density="comfortable"
                      size="small"
                      :icon="$vuetify.display.smAndUp ? undefined : 'mdi-email-open-multiple-outline'"
                      :prepend-icon="$vuetify.display.smAndUp ? 'mdi-email-open-multiple-outline' : undefined"
                      :text="$vuetify.display.smAndUp ? $t('Mark All as Read') : undefined"
                      :loading="markAllAsReadLoading"
                      @click.stop="markAllAsRead"
                    />
                  </template>
                </v-tooltip>
              </div>
            </template>
            <template v-slot:rightX>
              <div class="w-100 d-flex justify-end">
                <slot name="actions">

                  <!-- SHOW ALL MESSAGES IN A FORM-->
                  <v-dialog
                    v-if="perPage !== -1"
                    width="70%"
                    :height="dialogHeight"
                    v-model="dialogOpen"
                  >
                    <template v-slot:activator="{ props: activatorProps }">
                      <v-btn variant="elevated" density="comfortable" v-bind="activatorProps" class="mr-2">
                        {{ $t('Show All') }}
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card :class="[`bg-${background}`]" height="70vh">
                        <template #text class="pa-0">
                          <v-input-chat
                            :label="$t('All Messages')"
                            :background="background"
                            :modelValue="input"
                            :endpoints="endpoints"
                            :perPage="10"
                            :density="density"
                            :noSendAction="false"
                            :elevation="0"
                            :body-height="`calc(${dialogHeight} * 0.60)`"
                            :height="`calc(${dialogHeight} * 0.80)`"
                            :noStarring="noStarring"
                            :noPinning="noPinning"
                            :maxPinnedMessages="maxPinnedMessages"
                            :pinDurations="pinDurations"
                            v-bind="$attrs"
                          >
                            <template #actions>
                              <v-btn color="grey-darken-1" icon="mdi-close" size="default" variant="plain" density="compact" @click="dialogOpen = false"></v-btn>
                            </template>
                          </v-input-chat>
                        </template>
                      </v-card>

                    </template>
                  </v-dialog>

                  <!-- Show All ATTACHMENTS IN A DIALOG-->
                  <v-dialog v-if="uploadedAttachments.length > 0"
                    width="70%"
                    :height="dialogHeight"
                    v-model="uploadedAttachmentsDialog"
                  >
                    <template v-slot:activator="{ props: activatorProps }">
                      <v-btn variant="outlined" density="comfortable" v-bind="activatorProps" class="mr-2">
                        {{ $t('Show All Attachments') }}
                      </v-btn>
                    </template>
                    <template v-slot:default>
                      <v-card :class="[`bg-${background}`]" height="70vh">
                        <ue-title :text="$t('Attachments')" align="center" transform="none" padding="x-4"  class="w-100 pt-2" color="grey-darken-5">
                          <template #right>
                            <div class="d-flex w-100 justify-end">
                              <v-btn color="grey-darken-1" icon="mdi-close" size="default" variant="plain" density="compact" @click="uploadedAttachmentsDialog = false"></v-btn>
                            </div>
                          </template>
                        </ue-title>
                        <v-divider class="mx-4"></v-divider>
                        <v-card-text>
                          <v-infinite-scroll
                            :height="`calc(${dialogHeight} * 0.75)`"
                          >
                            <template v-for="attachment in uploadedAttachments">
                              <ue-filepond-preview
                                class="my-2"
                                :source="attachment"
                                image-size="24"
                                show-file-name
                                max-file-name-length="70"
                                no-overlay
                                show-date
                              />

                            </template>
                            <template v-slot:loading>

                            </template>
                          </v-infinite-scroll>
                        </v-card-text>

                      </v-card>

                    </template>
                  </v-dialog>

                  <!-- CONFIRM SEND MESSAGE -->
                  <v-btn variant="elevated" density="comfortable" @click="sendMessage">
                    {{ $t('Confirm') }}
                  </v-btn>
                </slot>
              </div>
            </template>
          </ue-title>
          <ue-title v-if="subtitle" :text="$t(subtitle)" align="center" transform="none" padding="a-0"  type="caption" weight="regular" color="grey-darken-4" l>

          </ue-title>

          <v-divider class="mt-2" v-if="!noDivider"></v-divider>

          <!-- PINNED MESSAGES: one shown at a time, clicking jumps to it and shows the next one (like WhatsApp) -->
          <v-sheet v-if="activePinnedMessage"
            id="PinnedMessage"
            elevation="3"
            class="v-input-chat__pinned bg-grey-lighten-4 mx-n4 px-4 py-2 d-flex align-center"
            style="z-index: 1000;"
            @click.stop="showNextPinnedMessage"
          >
            <!-- one segment per pinned message, the shown one highlighted -->
            <div v-if="pinnedMessages.length > 1" class="v-input-chat__pinned-indicator mr-3">
              <span
                v-for="(pinned, pinnedIndex) in pinnedMessages"
                :key="pinned.id"
                :class="[
                  'v-input-chat__pinned-indicator-segment',
                  pinnedIndex === activePinnedIndex ? 'bg-primary' : 'bg-grey-lighten-1'
                ]"
              />
            </div>

            <v-progress-circular v-if="jumpingToMessage" indeterminate size="16" width="2" color="primary" class="mr-3"/>
            <v-icon v-else size="small" color="primary" class="mr-3" style="transform: rotate(325deg)">mdi-pin</v-icon>

            <v-tooltip :text="activePinnedMessage.user_profile?.name" location="top">
              <template #activator="{ props: activatorProps }">
                <v-avatar :image="activePinnedMessage.user_profile?.avatar_url" size="24" class="mr-3" v-bind="activatorProps"/>
              </template>
            </v-tooltip>

            <div class="flex-grow-1 overflow-hidden">
              <div class="d-flex align-center text-body-2 text-grey-darken-4">
                <!-- messages with attachments are marked with a paperclip, like in the message list -->
                <v-icon
                  v-if="pinnedMessageAttachments(activePinnedMessage).length"
                  size="small"
                  color="grey-darken-1"
                  class="mr-1 flex-shrink-0"
                >mdi-paperclip</v-icon>
                <span class="text-truncate">{{ pinnedMessagePreview(activePinnedMessage) }}</span>
              </div>
            </div>
          </v-sheet>
        </v-card-title>

        <v-card-text class="mr-n2 mb-n4 mt-n4 pb-0">
          <v-infinite-scroll
            ref="infiniteScroll"
            :height="calculatedBodyHeight"
            mode="manual"
            side="start"
            @load="loadMoreMessages"
            :load-more-text="loadMoreText ? loadMoreText : $t('Load More Messages')"
          >
            <div
              v-for="(message, index) in formattedMessages"
              :key="index"
              :data-message-id="message.id"
              :class="[
                'v-input-chat__messages',
                editingMessage && !isEditingMessage(message) ? 'v-input-chat__messages--dimmed' : '',
                highlightedMessageId && highlightedMessageId === message.id ? 'v-input-chat__messages--highlighted' : ''
              ]"
              @click="editingMessage && !isEditingMessage(message) && cancelEditing()"
            >
              <slot name="message" :message="message" :index="index">
                <ChatMessage
                  :class="[
                    'v-input-chat__message',
                    message.loading  ? 'v-input-chat__message--loading' : '',
                  ]"
                  :modelValue="message"
                  @update:modelValue="updatedMessage($event, index)"
                  @edit="startEditing"
                  @delete="confirmDelete"
                  :editing="isEditingMessage(message)"
                  :reverse="message.reverse"
                  :updateEndpoint="endpoints.update"
                  :noStarring="noStarring"
                  :noPinning="noPinning"
                  :contentTruncateLength="contentTruncateLength"
                  :beforePin="preparePin"
                  v-bind="$attrs"
                />
              </slot>
            </div>
            <template v-slot:load-more="{ props }">
              <v-btn
                v-if="showLoadMoreButton"
                variant="outlined"
                v-bind="props"
              >
                {{ loadMoreText ? loadMoreText : $t('Load More Messages') }}
              </v-btn>
            </template>
            <template v-slot:empty="{ props }">

            </template>
          </v-infinite-scroll>

        </v-card-text>

        <v-input-filepond v-if="filepond"
          ref="inputFilepond"
          class="d-none"
          v-bind="invokeRule($lodash.omit(filepond, ['type', 'rules', 'rawRules']))"
          v-model="attachments"

          :xmodelValue="attachments"
          @xupdate:modelValue="handleModelValueUpdate"
          @loadingFile="handleLoadingFile"
          @loaded="handleFileLoaded"
          @processing="handleFileProcessing"
          @processed="handleFileProcessed"
          @error="handleFileError"
          @revert="handleFileRevert"
          @addfile="handleAddFile"
          @removefile="handleRemoveFile"
        >
          <template v-slot:activator="activatorProps">
          </template>
        </v-input-filepond>

        <!-- READ-ONLY NOTICE (e.g. closed tickets) -->
        <v-card-actions v-if="!noSendAction && isReadOnlyNoticeVisible" class="bg-surface v-input-chat__read-only">
          <slot name="read-only">
            <v-alert
              :color="disabledTextColor"
              variant="tonal"
              density="comfortable"
              class="w-100 text-center"
            >
              {{ $t(disabledText) }}
            </v-alert>
          </slot>
        </v-card-actions>

        <v-card-actions class="bg-surface" v-else-if="!noSendAction" id="messageBox">
          <slot name="sending">
            <v-textarea
              _class="position-absolute bg-surface px-4 pb-4"
              style="bottom: 0; left: 0; right: 0; z-index: 1000;"
              ref="messageBox"
              v-model="message"
              :variant="inputVariant"
              :disabled="loading"
              hide-details
              :placeholder="$t('Type here...')"
              density="compact"
              @click:append="sendMessage"
              @keydown.enter="handleEnterKey"
              @keydown.tab="handleTabKey"
              @keydown.esc="handleEscKey"
              @focus="handleFieldFocus"
              @blur="handleFieldBlur"
              :rows="textareaRows"
            >
              <template v-slot:prepend v-if="$vuetify.display.smAndUp || editingMessage">
                <!-- cancel editing, in place of the avatar -->
                <v-tooltip v-if="editingMessage" :text="$t('Cancel')" location="top">
                  <template v-slot:activator="{ props: activatorProps }">
                    <v-btn
                      v-bind="activatorProps"
                      icon="mdi-close"
                      variant="outlined"
                      color="grey-darken-1"
                      :size="$vuetify.display.smAndUp ? 40 : 32"
                      :disabled="loading"
                      @click="cancelEditing"
                    />
                  </template>
                </v-tooltip>
                <div v-else class="flex-grow-0">
                  <v-avatar class="my-aut" :image="$store.getters.userProfile.avatar_url" size="40"/>
                </div>
              </template>
              <template v-slot:append-inner>
                <!-- Custom attachment preview with hover delete buttons -->
                <div v-if="attachments.length > 0" class="d-flex align-center mr-2">
                  <div
                    v-for="(attachment, index) in attachments"
                    :key="attachment.id || index"
                    class="attachment-preview-wrapper position-relative mr-1"
                  >
                    <!-- Delete button - only visible on hover -->
                    <v-btn
                      icon="mdi-close"
                      size="x-small"
                      variant="text"
                      color="error"
                      class="attachment-delete-btn"
                      @click="removeAttachment(index)"
                    />

                    <!-- File preview -->
                    <ue-filepond-preview
                      :source="[attachment]"
                      image-size="32"
                    />
                  </div>
                </div>

                <v-btn v-if="!noEmoji" :disabled="loadingAttachment" :color="color" :size="$vuetify.display.smAndUp ? 'default' : 'small'" icon="mdi-emoticon-outline" density="compact" @click="openEmojiPicker" @focus="handleFieldFocus" @blur="handleFieldBlur" />
                <v-btn :color="color" :size="$vuetify.display.smAndUp ? 'default' : 'small'" icon="mdi-paperclip" density="compact" @click="handleAttachmentClick" @focus="handleFieldFocus" @blur="handleFieldBlur" />
                <v-btn
                  variant="elevated"
                  density="compact"
                  :icon="sendButtonIcon"
                  :size="$vuetify.display.smAndUp ? 'small' : 'x-small'"
                  :disabled="isSendButtonDisabled"
                  @click="sendMessage"
                  @focus="handleFieldFocus"
                  @blur="handleFieldBlur"
                />
              </template>
            </v-textarea>
          </slot>
        </v-card-actions>
      </v-card>

      <!-- DELETE MESSAGE CONFIRMATION -->
      <ue-modal
        :model-value="!!deletingMessage"
        @update:model-value="!$event && closeDeleteDialog()"
        width-type="sm"
        :title="$t('Delete message?')"
        :description="$t('This message will be deleted for everyone.')"
        :confirm-text="$t('Delete')"
        :cancel-text="$t('Cancel')"
        :confirm-loading="deleteLoading"
        :confirm-closing="false"
        :confirm-callback="deleteMessage"
        :confirm-button-attributes="{ color: 'error', variant: 'elevated' }"
        :reject-button-attributes="{ color: 'grey-darken-1', variant: 'outlined' }"
      />

      <!-- UNSTAR ALL CONFIRMATION -->
      <ue-modal
        v-model="unstarAllDialog"
        width-type="sm"
        :title="$t('Unstar all messages?')"
        :description="$t('All your starred messages in this chat will be unstarred.')"
        :confirm-text="$t('Unstar All')"
        :cancel-text="$t('Cancel')"
        :confirm-loading="unstarAllLoading"
        :confirm-closing="false"
        :confirm-callback="unstarAll"
        :confirm-button-attributes="{ color: 'secondary', variant: 'elevated' }"
        :reject-button-attributes="{ color: 'grey-darken-1', variant: 'outlined' }"
      />

      <!-- PIN DURATION SELECTION (like WhatsApp) -->
      <v-dialog
        :model-value="!!pinDurationRequest"
        @update:model-value="!$event && resolvePinDuration(null)"
        max-width="360"
      >
        <v-card class="pa-2">
          <v-card-title class="text-wrap">{{ $t('Choose how long your pin lasts') }}</v-card-title>
          <v-card-subtitle class="text-wrap">{{ $t('You can unpin at any time.') }}</v-card-subtitle>
          <v-card-actions class="d-flex flex-column align-stretch ga-1 mt-2">
            <v-btn
              v-for="duration in pinDurations"
              :key="duration.value"
              variant="text"
              color="primary"
              class="ma-0"
              block
              @click="resolvePinDuration(duration.value)"
            >
              {{ duration.title }}
            </v-btn>
            <v-btn
              variant="text"
              color="grey-darken-1"
              class="ma-0"
              block
              @click="resolvePinDuration(null)"
            >
              {{ $t('Cancel') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- REPLACE OLDEST PIN CONFIRMATION -->
      <ue-modal
        :model-value="!!pinReplacement"
        @update:model-value="!$event && resolvePinReplacement(false)"
        width-type="sm"
        :title="$t('Replace oldest pin?')"
        :description="$t('Your new pin will replace the oldest one.')"
        :confirm-text="$t('Replace')"
        :cancel-text="$t('Cancel')"
        :confirm-closing="false"
        :confirm-callback="() => resolvePinReplacement(true)"
        :confirm-button-attributes="{ color: 'primary', variant: 'elevated' }"
        :reject-button-attributes="{ color: 'grey-darken-1', variant: 'outlined' }"
      />

      <!-- EMOJI COMPONENT -->
      <ue-emojis
        v-if="!noEmoji"
        v-model="showEmojiPicker"
        :disabled="loadingAttachment"
        @emoji-selected="insertEmoji"
      />

      <!-- UPLOAD PROGRESS MODAL DIALOG -->
      <v-dialog
        v-model="loadingAttachment"
        persistent
        width="400"
        max-width="90vw"
        transition="dialog-bottom-transition"
        :retain-focus="false"
      >
        <v-card class="upload-modal-card">
          <v-card-text class="text-center pa-8">
            <v-progress-circular
              :size="64"
              :width="6"
              color="primary"
              indeterminate
              class="mb-6"
            />
            <div class="text-h5 mb-3 text-primary font-weight-medium">
              {{ $t('Uploading Attachment') }}
            </div>
            <div class="text-body-1 text-medium-emphasis mb-4">
              {{ $t('Please wait while your file is being processed...') }}
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>
    </template>
  </v-input>
</template>

<script>
  import { useInput, makeInputProps, makeInputEmits, useValidation } from '@/hooks'
  import ChatMessage from '@/components/others/ChatMessage.vue';
  import { ALERT, RETAINABLE_NOTIFICATION } from '@/store/mutations'
  export default {
    name: 'v-input-chat',
    emits: [...makeInputEmits],
    components: {
      ChatMessage
    },
    props: {
      ...makeInputProps(),
      subtitle: {
        type: String,
        default: null
      },
      color: {
        type: String,
      },
      disabled: {
        type: Boolean,
        default: false
      },
      height: {
        type: String,
        default: '50vh'
      },
      background: {
        type: String,
        // default: 'grey-lighten-5'
      },
      elevation: {
        type: Number,
        default: 2
      },
      density: {
        type: String,
        default: 'comfortable'
      },
      variant: {
        type: String,
        default: 'plain'
      },
      endpoints: {
        type: Object,
        default: () => ({})
      },
      perPage: {
        type: Number,
        default: -1
      },
      initialPage: {
        type: Number,
        default: 1
      },
      filepond: {
        type: Object,
        default: null
      },
      noSendAction: {
        type: Boolean,
        default: false
      },
      noBackground: {
        type: Boolean,
        default: false
      },
      dialogHeight: {
        type: String,
        default: '80vh'
      },
      bodyHeight: {
        type: String,
        default: null
      },
      loadMoreText: {
        type: String,
        default: null
      },
      refreshTime: {
        type: Number,
        default: 10000
      },
      inputVariant: {
        type: String,
        default: 'outlined'
      },
      noStarring: {
        type: Boolean,
        default: false
      },
      noPinning: {
        type: Boolean,
        default: false
      },
      maxPinnedMessages: {
        type: Number,
        default: 4
      },
      // selectable pin durations in hours, e.g. [{ value: 24, title: '24 hours' }]; empty pins until unpinned
      pinDurations: {
        type: Array,
        default: () => [
          { value: 24, title: '24 hours' },
          { value: 24 * 7, title: '7 days' },
          { value: 24 * 30, title: '30 days' },
        ]
      },
      contentTruncateLength: {
        type: Number,
        default: 50
      },
      noDivider: {
        type: Boolean,
        default: false
      },
      noTab: {
        type: Boolean,
        default: false
      },
      noEmoji: {
        type: Boolean,
        default: false
      },
      maxMessageboxRows: {
        type: Number,
        default: 5
      },
      // shown in place of the message box while the chat is disabled
      disabledText: {
        type: String,
        default: null
      },
      disabledTextColor: {
        type: String,
        default: 'success'
      }
    },
    setup (props, context) {
      const { invokeRule } = useValidation()
      return {
        ...useInput(props, context),
        invokeRule
      }
    },
    data: function () {
      return {
        message: '',
        messages: [],
        loading: true,
        loadingAttachment: false,

        page: this.initialPage,
        lastPage: 1,
        dialogOpen: false,
        textareaRows: 1,

        attachments: [],
        uploadedAttachments: [],
        uploadedAttachmentsDialog: false,

        refreshInterval: null,
        chatChannelName: null,
        // pinned messages, the most recently pinned first
        pinnedMessages: [],
        activePinnedIndex: 0,
        pinnedMessagesRequest: 0,
        highlightedMessageId: null,
        // starred messages are shared by all participants of the chat
        starredCount: 0,
        highlightTimer: null,
        // older pages are being loaded to reach a pinned message
        jumpingToMessage: false,
        showEmojiPicker: false,
        // pending pin waiting for the "replace oldest pin" confirmation: { message, oldest, resolve }
        pinReplacement: null,
        // pending pin waiting for its duration to be chosen: { message, resolve }
        pinDurationRequest: null,

        editingMessage: null,
        draftBeforeEditing: null,
        deletingMessage: null,
        deleteLoading: false,
        syncedAt: null,

        markAllAsReadLoading: false,
        unstarAllDialog: false,
        unstarAllLoading: false,
        serverUnreadCount: 0,

      }
    },
    computed: {
      currentUser() {
        return this.$store.getters.userProfile;
      },
      canUseBroadcastSync() {
        const ns = import.meta.env.VUE_APP_NAME
        const broadcastEnabled = window[ns]?.STORE?.broadcast?.enabled !== false

        return broadcastEnabled
          && typeof window !== 'undefined'
          && window.Echo
          && typeof window.Echo.private === 'function'
      },
      formattedMessages() {
        return this.formatMessages(this.messages);
      },
      activePinnedMessage() {
        return this.pinnedMessages[this.activePinnedIndex] ?? this.pinnedMessages[0] ?? null;
      },
      unreadCount() {
        return this.formattedMessages.filter(message => !message.reverse && !message.is_read).length;
      },
      hasStarredMessages() {
        return !this.noStarring && !!this.endpoints.starredMessages && this.input > -1;
      },
      canUnstarAll() {
        return this.hasStarredMessages && !!this.endpoints.unstarAll && this.starredCount > 0;
      },
      canMarkAllAsRead() {
        // the server count also covers unread messages not loaded yet (paginated chats)
        return !!this.endpoints.readAll && (this.unreadCount > 0 || this.serverUnreadCount > 0);
      },
      noRemainingItems() {
        return !this.loading && (this.perPage > -1 && this.page === this.lastPage);
      },
      // hidden while the chat is empty: nothing older to load, and loading starts as true before the first fetch
      showLoadMoreButton() {
        return this.isInfiniteScrollable
          && this.messages.length > 0
          && !this.noRemainingItems;
      },
      isInfiniteScrollable() {
        return this.perPage > -1;
      },
      isReadOnlyNoticeVisible() {
        return this.disabled && !!this.disabledText;
      },
      isMessageEmpty() {
        return !this.message || !this.message.trim();
      },
      isSendButtonDisabled() {
        // Allow sending if there are attachments (even without text)
        const hasAttachments = this.attachments && this.attachments.length > 0;
        const hasText = this.message && this.message.trim();
        // console.log('message', this.message)
        // console.log('trim', this.message.trim())
        // console.log('hasAttachments', hasAttachments)
        // Enable if there are attachments OR text, and not loading
        return this.loading || this.loadingAttachment || (!hasAttachments && !hasText);
      },
      sendButtonIcon() {
        if (this.isSendButtonDisabled) {
          return 'mdi-send-lock';
        }

        const hasAttachments = this.attachments && this.attachments.length > 0;
        const hasText = this.message && this.message.trim();

        if (this.editingMessage) {
          return 'mdi-check';
        }

        if (hasAttachments || hasText) {
          return 'mdi-send-check';
        }

        return 'mdi-send';
      },
      calculatedBodyHeight() {
        let extraHeight = 0;
        let minusHeight = '';

        if(this.activePinnedMessage) {
          extraHeight += this.$jquery('#PinnedMessage').outerHeight(true);
        }

        if(this.textareaRows > 0) {
          const messageBoxHeight = this.$jquery('#messageBox').height();
          extraHeight += this.textareaRows * 20;
        }

        if(extraHeight > 0) {
          minusHeight = ` - ${extraHeight}px`;
        }

        return this.bodyHeight ? `calc(${this.bodyHeight}${minusHeight})` : `calc(${this.height}*0.65${minusHeight})`;
      }

    },
    watch: {
      attachments: {
        handler(newValue) {
          // console.log('Attachments changed:', newValue);

          // Check if all attachments have serverId (uploaded)
          if (newValue && newValue.length > 0) {
            const allUploaded = newValue.every(attachment => {
              // Check multiple indicators that file is ready
              const hasServerId = attachment.serverId;
              const hasPreview = attachment.preview || attachment.dataURL;
              const hasSource = attachment.source;
              const isComplete = attachment.status === 'success' || attachment.status === 'complete';

              // console.log('Attachment check:', {
              //   id: attachment.id,
              //   filename: attachment.filename || attachment.name,
              //   hasServerId,
              //   hasPreview,
              //   hasSource,
              //   isComplete,
              //   status: attachment.status
              // });

              return hasServerId || hasPreview || hasSource || isComplete;
            });

            if (allUploaded && this.loadingAttachment) {
              // console.log('All attachments ready, closing dialog');
              this.loadingAttachment = false;

              // Focus on textarea after all attachments are ready
              this.$nextTick(() => {
                this.focusOnTextarea();
              });
            }
          }
        },
        deep: true
      },
      message: {
        handler(newVal) {
          if(newVal.includes('\n')) {
            // count the number of \n in newVal
            const count = (newVal.match(/\n/g) || []).length;
            this.textareaRows = count > this.maxMessageboxRows ? this.maxMessageboxRows : count;
          } else {
            this.textareaRows = 1;
          }
        },
      }
    },
    methods: {
      scrollEndInfiniteScroll() {
        this.$nextTick(() => {
          const container = this.$refs.infiniteScroll.$el;
          container.scrollTop = container.scrollHeight;
        });
      },
      formatMessage(message) {
        message = {
          user_profile: this.$lodash.cloneDeep(this.currentUser),
          attachments: [],
          reverse: false,
          loading: false,
          ...message,
        }

        if(message.user_profile.id === this.currentUser.id) {
          message.reverse = true;
          message.is_read = true;
          message.user_profile.name = this.$t('You');
        }

        return message;
      },
      formatMessages(messages) {
        return messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
          .map(message => this.formatMessage(message));
      },
      loadMessages(done) {
        const endpoint = this.endpoints.index.replace(':id', this.input);

        let params = {};

        if (this.perPage !== -1) {
          params.page = this.page;
          params.perPage = this.perPage;
        }

        this.loading = true;

        return axios.get(endpoint, { params }).then(response => {
          if(response.status === 200) {
            this.syncedAt ??= response.headers['x-chat-synced-at'] ?? null;
            this.setServerUnreadCount(response);
            this.checkPinnedMessages(response);
            this.setStarredCount(response);

            if (this.perPage > -1) {
              const total = response.data.total;
              this.lastPage = response.data.last_page;
              this.page = response.data.current_page;

              this.messages = [...response.data.data, ...this.messages]

              if(total !== 0) {
                if(done) {
                  if(this.page === this.lastPage) {
                    done('empty');
                  } else {
                    done('ok');
                  }
                } else{

                }

              } else if(done) {
                done('empty');
              }
            } else {
              this.messages = this.formatMessages(response.data);
              done('empty');
            }
          }
        }).finally(() => {
          this.loading = false;

          if(!this.isInfiniteScrollable || this.page === 1) {
            this.scrollEndInfiniteScroll();
          }
        });
      },
      loadMoreMessages({done}) {
        this.page++;
        this.loadMessages(done);
      },
      addMessage(message) {
        return this.messages.push(message) - 1;
      },
      findMessageByTempId(tempId) {
        return this.messages.findIndex(message => message.tempId === tempId);
      },
      updateMessageByTempId(tempId, newMessage) {
        this.messages = this.messages.filter(message => message.id !== newMessage.id);
        const index = this.findMessageByTempId(tempId);
        if (index !== -1) {
          this.messages[index] = {
            ...this.messages[index],
            ...newMessage
          };
        }
      },
      sendMessage() {
        if (this.editingMessage) {
          return this.submitEdit();
        }

        // Check if there's content to send (text or attachments)
        const hasAttachments = this.attachments && this.attachments.length > 0;
        const hasText = this.message && this.message.trim();

        if (!hasAttachments && !hasText) {
          return;
        }

        const endpoint = this.endpoints.store.replace(':id', this.input);

        const newMessage = {
          loading: true,
          content: this.message, // Fallback content for attachment-only messages
          tempId: Date.now(), // Add unique tempId to identify this message later
          attachments: [...this.attachments] // Ensure attachments are included in the temporary message
        };
        let tempId = newMessage.tempId;
        this.addMessage(newMessage);

        this.loading = true;

        // Log the request payload for debugging
        const requestPayload = {
          content: this.message, // Fallback content for attachment-only messages
          attachments: this.attachments
        };
        // console.log('Sending message with payload:', requestPayload);

        axios.post(endpoint, requestPayload).then(response => {
          if (response.status === 200) {

            let message = response.data
            message.loading = false;
            this.updateMessageByTempId(tempId, message);
            // this.messages[newIndex].loading = false;
            // this.messages[newIndex].id = response.data.id;
            // this.messages[newIndex].created_at = response.data.created_at;
            // this.messages[newIndex].attachments = response.data.attachments ?? [];
            this.message = '';
            this.attachments = [];
          }
        }).catch(error => {
          console.error('Error sending message:', error);
          console.error('Request payload was:', requestPayload);
          console.error('Response data:', error.response?.data);

          // Remove the failed message from the UI
          this.updateMessageByTempId(tempId, { loading: false, error: true });

          // Show user-friendly error message
          if (error.response?.status === 500) {
            console.error('Server error - this might be a backend validation issue');
          }
        }).finally(() => {
          this.loading = false;
          this.scrollEndInfiniteScroll();
          // Focus back on the textarea after sending message
          this.$nextTick(() => {
            if (this.$refs.messageBox) {
              this.$refs.messageBox.focus();
            }
          });
        });
      },
      getAttachments() {
        const endpoint = this.endpoints.attachments.replace(':id', this.input);
        axios.get(endpoint).then(response => {
          this.uploadedAttachments = response.data;
        });
      },
      /**
       * Load the pinned messages and sync the pin status of the loaded messages with them,
       * e.g. the oldest pin is unpinned by the server when the limit is exceeded.
       */
      fetchPinnedMessages({ showLatest = false } = {}) {
        if (!this.endpoints.pinnedMessages) return;

        const endpoint = this.endpoints.pinnedMessages.replace(':id', this.input);
        // only the latest request may update the state
        const request = ++this.pinnedMessagesRequest;

        axios.get(endpoint).then(response => {
          if (request !== this.pinnedMessagesRequest || response.status !== 200) return;

          this.pinnedMessages = Array.isArray(response.data) ? response.data : [];

          if (showLatest) {
            this.activePinnedIndex = 0;
          }

          this.clampActivePinnedIndex();
          this.syncPinnedStatus();
        });
      },
      // reload the pinned messages when the server reports a different set of pins
      checkPinnedMessages(response) {
        const header = response.headers['x-chat-pinned-ids'];

        if (header === undefined) return;

        const serverIds = header;
        const currentIds = this.pinnedMessages.map(pinned => pinned.id).join(',');

        if (serverIds !== currentIds) {
          this.fetchPinnedMessages();
        }
      },
      syncPinnedStatus() {
        const pinnedIds = new Set(this.pinnedMessages.map(pinned => pinned.id));

        if (!this.messages.some(message => message.id && !!message.is_pinned !== pinnedIds.has(message.id))) return;

        this.messages = this.messages.map(message =>
          message.id && !!message.is_pinned !== pinnedIds.has(message.id)
            ? { ...message, is_pinned: pinnedIds.has(message.id) }
            : message
        );
      },
      clampActivePinnedIndex() {
        if (this.activePinnedIndex >= this.pinnedMessages.length) {
          this.activePinnedIndex = 0;
        }
      },
      // attachments may come as an object keyed by index (a filtered Laravel collection)
      pinnedMessageAttachments(message) {
        const attachments = message?.attachments;

        if (!attachments) return [];

        return Array.isArray(attachments) ? attachments : Object.values(attachments);
      },
      pinnedMessagePreview(message) {
        if (message?.content) return message.content;

        const attachments = this.pinnedMessageAttachments(message);
        const attachment = attachments[0];
        const name = attachment?.file_name ?? attachment?.name ?? this.$t('Attachment');

        return attachments.length > 1 ? `${name} +${attachments.length - 1}` : name;
      },
      showNextPinnedMessage() {
        if (!this.activePinnedMessage || this.jumpingToMessage) return;

        this.scrollToMessage(this.activePinnedMessage.id);

        if (this.pinnedMessages.length > 1) {
          this.activePinnedIndex = (this.activePinnedIndex + 1) % this.pinnedMessages.length;
        }
      },
      findMessageElement(id) {
        return this.$el?.querySelector?.(`[data-message-id="${id}"]`) ?? null;
      },
      async scrollToMessage(id) {
        if (this.jumpingToMessage) return;

        let element = this.findMessageElement(id);

        // older messages of paginated chats may not be loaded yet, load the previous pages until the message shows up
        if (!element && this.perPage > -1) {
          this.jumpingToMessage = true;

          try {
            while (!element && this.page < this.lastPage) {
              if (this.loading) break;

              this.page++;
              await this.loadMessages();
              await this.$nextTick();

              element = this.findMessageElement(id);
            }
          } catch (error) {
            console.error('Error loading older messages:', error);
          } finally {
            this.jumpingToMessage = false;
          }
        }

        if (!element) return;

        element.scrollIntoView({ block: 'center', behavior: 'smooth' });

        // reset first so the highlight animation restarts when the same message is jumped to again
        clearTimeout(this.highlightTimer);
        this.highlightedMessageId = null;

        await this.$nextTick();

        this.highlightedMessageId = id;
        this.highlightTimer = setTimeout(() => {
          this.highlightedMessageId = null;
        }, 2500);
      },
      refreshMessages() {
        const endpoint = this.endpoints.index.replace(':id', this.input);

        let created_at = new Date().toISOString();

        if(this.messages.length) {
          let lastMessage = this.messages.filter(message => !!message.created_at)
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];

          if(lastMessage && lastMessage.created_at) {
            created_at = lastMessage.created_at;
          }
        }


        axios.get(endpoint, {
          params: {
            // from: new Date(lastMessage.created_at).getTime(),
            from: created_at,
            user_id: this.currentUser.id,
            // also fetch already-loaded messages changed since the last sync (edited, read, ...)
            ...(this.syncedAt ? { updated_from: this.syncedAt } : {}),
          }
        }).then(response => {
          if(response.status === 200) {
            this.syncedAt = response.headers['x-chat-synced-at'] ?? this.syncedAt;
            this.setServerUnreadCount(response);
            this.checkPinnedMessages(response);
            this.setStarredCount(response);

            if(response.data.length > 0) {
              this.mergeMessages(response.data);
              this.getAttachments();
            }
          }

          if(response.status === 401) {
            this.stopRefreshPolling();
          }
        });
      },
      startRefreshPolling() {
        if (this.refreshInterval) {
          return
        }

        this.refreshInterval = setInterval(() => {
          this.refreshMessages();
        }, this.refreshTime);
      },
      stopRefreshPolling() {
        if (this.refreshInterval) {
          clearInterval(this.refreshInterval);
          this.refreshInterval = null;
        }
      },
      /**
       * Clear retainable tray items for this chat when the user opens it.
       * Matches backend `ChatableMessageBroadcastNotification::getRetainGroup()`.
       */
      dismissRetainableForChat() {
        if (!this.input || this.input <= -1) {
          return
        }

        this.$store?.commit?.(RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP, `chat:${this.input}`)
      },
      subscribeChatChannel() {
        if (!this.canUseBroadcastSync || !this.input || this.input <= -1) {
          return false
        }

        const channelName = `chats.${this.input}`
        this.chatChannelName = channelName

        window.Echo.private(channelName)
          .listen('.modularous.chatable.message.synced', (payload) => {
            this.handleChatMessageSynced(payload)
          })

        this.dismissRetainableForChat()

        return true
      },
      unsubscribeChatChannel() {
        if (this.chatChannelName && window.Echo && typeof window.Echo.leave === 'function') {
          window.Echo.leave(this.chatChannelName)
        }
        this.chatChannelName = null
      },
      handleChatMessageSynced(payload) {
        const action = payload?.action
        const message = payload?.message
        if (!action || !message) {
          return
        }

        if (action === 'created') {
          const existingIndex = this.messages.findIndex((m) => m.id && m.id === message.id)
          if (existingIndex !== -1) {
            this.messages.splice(existingIndex, 1, this.formatMessage({
              ...this.messages[existingIndex],
              ...message,
              loading: false,
              tempId: undefined,
            }))
          } else {
            this.addMessage(this.formatMessage(message))
          }

          if (message.attachments?.length) {
            this.getAttachments()
          }

          this.$nextTick(() => {
            this.scrollEndInfiniteScroll()
          })

          return
        }

        if (action === 'updated') {
          const index = this.messages.findIndex((m) => m.id === message.id)
          if (index !== -1) {
            this.messages.splice(index, 1, this.formatMessage({
              ...this.messages[index],
              ...message,
              loading: false,
            }))
          }

          return
        }

        if (action === 'deleted') {
          this.messages = this.messages.filter((m) => m.id !== message.id)
        }
      },
      mergeMessages(incomingMessages) {
        const messages = [...this.messages];

        incomingMessages.forEach(incoming => {
          const index = messages.findIndex(message => message.id === incoming.id);

          // deleted since the last sync: drop it
          if (incoming.deleted_at) {
            if (index !== -1) messages.splice(index, 1);
            this.handleMessageRemoved(incoming.id);
            return;
          }

          if (index !== -1) {
            messages[index] = { ...messages[index], ...incoming };
          } else {
            messages.push(incoming);
          }

          // the message being edited was read (or otherwise locked) in the meantime
          if (this.editingMessage && this.editingMessage.id === incoming.id && !incoming.is_editable) {
            this.abortEditing(this.$t('This message can no longer be edited.'));
          }
        });

        this.messages = messages;
      },
      setStarredCount(response) {
        const count = response.headers['x-chat-starred-count'];

        if (count === undefined) return;

        this.starredCount = Number(count) || 0;
      },
      async jumpToStarredMessage(message) {
        this.$refs.starredBrowser?.closeDialog();

        await this.$nextTick();

        this.scrollToMessage(message.id);
      },
      starredMessageSender(message) {
        const profile = message.user_profile;

        return profile && this.currentUser && profile.id === this.currentUser.id
          ? this.$t('You')
          : (profile?.name ?? '');
      },
      starredMessageDate(message) {
        return message.created_at ? this.$d(new Date(message.created_at), 'short') : '';
      },
      setServerUnreadCount(response) {
        const count = response.headers['x-chat-unread-count'];

        if (count !== undefined) {
          this.serverUnreadCount = Number(count) || 0;
        }
      },
      markAllAsRead() {
        if (!this.endpoints.readAll || this.markAllAsReadLoading) return;

        const endpoint = this.endpoints.readAll.replace(':id', this.input);

        this.markAllAsReadLoading = true;

        axios.put(endpoint).then(() => {
          // own messages keep their status, it reflects whether the other party has read them
          this.messages = this.messages.map(message =>
            message.user_profile && message.user_profile.id !== this.currentUser.id
              ? { ...message, is_read: true }
              : message
          );
          this.serverUnreadCount = 0;
        }).catch(error => {
          console.error('Error marking messages as read:', error);
          this.$store.commit(ALERT.SET_ALERT, {
            message: error.response?.data?.message ?? this.$t('Messages could not be marked as read.'),
            variant: 'error',
          });
        }).finally(() => {
          this.markAllAsReadLoading = false;
        });
      },
      unstarAll() {
        if (!this.endpoints.unstarAll || this.unstarAllLoading) return;

        const endpoint = this.endpoints.unstarAll.replace(':id', this.input);

        this.unstarAllLoading = true;

        axios.put(endpoint).then(() => {
          this.messages = this.messages.map(message =>
            message.is_starred ? { ...message, is_starred: false } : message
          );
          this.starredCount = 0;
          this.unstarAllDialog = false;
        }).catch(error => {
          console.error('Error unstarring messages:', error);
          this.$store.commit(ALERT.SET_ALERT, {
            message: error.response?.data?.message ?? this.$t('Messages could not be unstarred.'),
            variant: 'error',
          });
        }).finally(() => {
          this.unstarAllLoading = false;
        });
      },
      /**
       * Called before pinning: asks how long the pin lasts, then confirms replacing the oldest pin
       * when the limit is reached. Resolves to false to cancel, or to the extra request data.
       */
      async preparePin(message) {
        let pinDuration = null;

        if (this.pinDurations.length) {
          pinDuration = await this.choosePinDuration(message);

          if (!pinDuration) return false;
        }

        if (!(await this.confirmPinReplacement(message))) return false;

        return pinDuration ? { pin_duration: pinDuration } : true;
      },
      // resolves to the chosen duration in hours, or null when cancelled
      choosePinDuration(message) {
        this.resolvePinDuration(null);

        return new Promise(resolve => {
          this.pinDurationRequest = { message, resolve };
        });
      },
      resolvePinDuration(duration) {
        if (!this.pinDurationRequest) return;

        this.pinDurationRequest.resolve(duration);
        this.pinDurationRequest = null;
      },
      // resolves to true when the message can be pinned, asking first when the pin limit is reached
      confirmPinReplacement(message) {
        const otherPins = this.pinnedMessages.filter(pinned => pinned.id !== message.id);

        if (otherPins.length < this.maxPinnedMessages) return Promise.resolve(true);

        this.resolvePinReplacement(false);

        return new Promise(resolve => {
          // pinned messages are ordered the most recently pinned first
          this.pinReplacement = { message, oldest: otherPins[otherPins.length - 1], resolve };
        });
      },
      resolvePinReplacement(confirmed) {
        if (!this.pinReplacement) return;

        this.pinReplacement.resolve(confirmed);
        this.pinReplacement = null;
      },
      confirmDelete(message) {
        if (!message?.id || this.noSendAction) return;

        this.deletingMessage = message;
      },
      closeDeleteDialog() {
        if (this.deleteLoading) return;

        this.deletingMessage = null;
      },
      handleMessageRemoved(id) {
        if (this.pinnedMessages.some(pinned => pinned.id === id)) {
          this.pinnedMessages = this.pinnedMessages.filter(pinned => pinned.id !== id);
          this.clampActivePinnedIndex();
        }

        if (this.editingMessage?.id === id) {
          this.cancelEditing();
        }

        if (this.deletingMessage?.id === id && !this.deleteLoading) {
          this.deletingMessage = null;
        }
      },
      deleteMessage() {
        if (!this.deletingMessage || this.deleteLoading) return;

        const messageId = this.deletingMessage.id;
        const endpoint = this.endpoints.destroy.replace(':id', messageId);

        this.deleteLoading = true;

        axios.delete(endpoint).then(() => {
          this.messages = this.messages.filter(message => message.id !== messageId);
          this.deleteLoading = false;
          this.handleMessageRemoved(messageId);
          this.getAttachments();
        }).catch(error => {
          if (error.response?.status === 403) {
            const index = this.messages.findIndex(message => message.id === messageId);
            if (index !== -1) {
              this.messages[index] = { ...this.messages[index], is_deletable: false, is_editable: false, editable_until: null };
            }

            this.$store.commit(ALERT.SET_ALERT, {
              message: error.response?.data?.message ?? this.$t('This message can no longer be deleted.'),
              variant: 'warning',
            });
          } else {
            console.error('Error deleting message:', error);
          }
        }).finally(() => {
          this.deleteLoading = false;
          this.deletingMessage = null;
        });
      },
      isEditingMessage(message) {
        return !!this.editingMessage && !!message?.id && this.editingMessage.id === message.id;
      },
      startEditing(message) {
        if (!message?.id || this.noSendAction) return;

        // already editing this message: keep the changes made in the composer (e.g. removed attachments)
        if (this.editingMessage?.id === message.id) {
          this.$nextTick(() => this.focusOnTextarea());
          return;
        }

        // keep the unsent draft so it can be restored after editing
        if (!this.editingMessage) {
          this.draftBeforeEditing = {
            message: this.message,
            attachments: [...this.attachments],
          };
        }

        this.editingMessage = message;
        this.message = message.content ?? '';
        this.attachments = this.$lodash.cloneDeep(message.attachments ?? []);

        this.$nextTick(() => {
          this.focusOnTextarea();
          this.scrollToEditingMessage();
        });
      },
      scrollToEditingMessage() {
        const element = this.$el?.querySelector?.(`[data-message-id="${this.editingMessage?.id}"]`);

        element?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      },
      restoreDraft() {
        this.message = this.draftBeforeEditing?.message ?? '';
        this.attachments = this.draftBeforeEditing?.attachments ?? [];
        this.draftBeforeEditing = null;
      },
      cancelEditing() {
        if (!this.editingMessage || this.loading) return;

        this.editingMessage = null;
        this.restoreDraft();
      },
      abortEditing(reason) {
        // keep the typed text so it can still be sent as a new message
        this.editingMessage = null;
        this.draftBeforeEditing = null;

        if (reason) {
          this.$store.commit(ALERT.SET_ALERT, { message: reason, variant: 'warning' });
        }
      },
      hasEditChanges() {
        if (!this.editingMessage) return false;

        const originalContent = (this.editingMessage.content ?? '').trim();
        const currentContent = (this.message ?? '').trim();

        if (originalContent !== currentContent) return true;

        const uuids = attachments => (attachments ?? [])
          .map(attachment => attachment.uuid)
          .filter(Boolean)
          .sort();

        const originalUuids = uuids(this.editingMessage.attachments);
        const currentUuids = uuids(this.attachments);

        return originalUuids.length !== currentUuids.length
          || originalUuids.some((uuid, index) => uuid !== currentUuids[index]);
      },
      submitEdit() {
        const hasAttachments = this.attachments && this.attachments.length > 0;
        const hasText = this.message && this.message.trim();

        if (!this.editingMessage || (!hasAttachments && !hasText)) {
          return;
        }

        // nothing changed: just leave edit mode, the message must not be marked as edited
        if (!this.hasEditChanges()) {
          this.cancelEditing();
          this.$nextTick(() => this.focusOnTextarea());
          return;
        }

        const messageId = this.editingMessage.id;
        const endpoint = this.endpoints.edit.replace(':id', messageId);

        this.loading = true;

        axios.put(endpoint, {
          content: this.message,
          attachments: this.attachments,
        }).then(response => {
          if (response.status === 200) {
            this.editingMessage = null;
            this.mergeMessages([{ ...response.data, loading: false }]);
            this.restoreDraft();
          }
        }).catch(error => {
          const status = error.response?.status;

          if (status === 403) {
            const index = this.messages.findIndex(message => message.id === messageId);
            if (index !== -1) {
              this.messages[index] = { ...this.messages[index], is_editable: false, editable_until: null };
            }

            this.abortEditing(error.response?.data?.message ?? this.$t('This message can no longer be edited.'));
          } else if (status === 422) {
            this.$store.commit(ALERT.SET_ALERT, { message: error.response?.data?.message, variant: 'warning' });
          } else {
            console.error('Error editing message:', error);
          }
        }).finally(() => {
          this.loading = false;
          this.$nextTick(() => this.focusOnTextarea());
        });
      },
      handleEnterKey(event) {
        // If Shift is pressed, allow default behavior (new line)
        if (event.shiftKey) {
          return;
        }

        // If only Enter is pressed, prevent default and send message
        // Allow sending with just attachments (no text required)
        event.preventDefault();
        this.sendMessage();
      },
      handleEscKey(event) {
        if (!this.editingMessage) return;

        // keep Escape from reaching an enclosing dialog (Vuetify listens on window), only the editing is cancelled
        event.stopPropagation();
        event.preventDefault();
        this.cancelEditing();
      },
      handleTabKey(event) {
        // If noTab is true, prevent default tab behavior (navigation)
        if (this.noTab) {
          return;
        }

        // Prevent default tab behavior (navigation)
        event.preventDefault();

        // Insert tab character at cursor position
        const textarea = event.target;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        // Insert tab character
        const newValue = this.message.substring(0, start) + '\t' + this.message.substring(end);

        // Update the message
        this.message = newValue;

        // Set cursor position after the tab
        this.$nextTick(() => {
          textarea.selectionStart = textarea.selectionEnd = start + 1;
        });
      },
      updatedMessage(event) {

        let messages = [...this.messages];
        let index = messages.findIndex(message => message.id === event.id);
        let oldMessage = this.messages[index];

        const pinChanged = !!oldMessage && !!oldMessage.is_pinned !== !!event.is_pinned;
        const starChanged = !!oldMessage && !!oldMessage.is_starred !== !!event.is_starred;

        if(index !== -1) {
          messages[index] = {
            ...messages[index],
            ...event
          };
        }

        this.messages = messages;

        if (starChanged) {
          this.starredCount = Math.max(0, this.starredCount + (event.is_starred ? 1 : -1));
        }

        // the server may have unpinned the oldest pin to stay within the limit
        if (pinChanged) {
          this.fetchPinnedMessages({ showLatest: !!event.is_pinned });
        }
      },
      insertEmoji(emoji) {
        // Insert emoji at cursor position
        const textarea = this.$refs.messageBox;
        if (textarea) {
          const start = textarea.selectionStart;
          const end = textarea.selectionEnd;

          // Insert emoji at cursor position
          const newValue = this.message.substring(0, start) + emoji + this.message.substring(end);
          this.message = newValue;

          // Set cursor position after the emoji and focus on textarea
          this.$nextTick(() => {
            textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
            textarea.focus();
            // console.log('Focus set on textarea after emoji insertion');
          });
        }

        // Close the emoji picker
        this.showEmojiPicker = false;
      },
      openEmojiPicker() {
        this.showEmojiPicker = true;
        // console.log('Emoji picker opened:', this.showEmojiPicker); // Debug log
      },
      removeAttachment(index) {
        // Remove attachment at the specified index
        this.attachments.splice(index, 1);
        // console.log('Removed attachment at index:', index, 'Remaining attachments:', this.attachments);
      },
      handleLoadingFile(file) {
        // Handle when a file starts loading
        // console.log('File loading started:', file);
        this.loadingAttachment = true;

        // Fallback: close dialog after 10 seconds if events don't fire
        setTimeout(() => {
          if (this.loadingAttachment) {
            // console.log('Fallback: closing dialog after timeout');
            this.loadingAttachment = false;
          }
        }, 5000);
      },

      // Handle file selection before loading starts
      handleFileSelected() {
        // Immediately set loading state when file is selected
        // console.log('File selected, setting loading state immediately');
        this.loadingAttachment = true;
      },

            // Handle attachment button click
      handleAttachmentClick() {
        // console.log('Attachment button clicked, opening file picker');

        // Open file picker first, then set loading state when file is actually selected
        this.$refs.inputFilepond.browse();

        // Add a listener to detect if file picker is cancelled
        this.detectFilePickerCancellation();
      },

      // Detect if file picker is cancelled
      detectFilePickerCancellation() {
        // Check after a short delay if any files were added
        setTimeout(() => {
          if (this.attachments.length === 0 && this.loadingAttachment) {
            // console.log('File picker cancelled or closed without selection');
            this.loadingAttachment = false;
          }
        }, 1000); // Check after 1 second
      },

      // Handle model value updates from filepond
      handleModelValueUpdate(event) {
        // console.log('Model value update:', event);

        // If attachments are being added, set loading state immediately
        if (event && event.length > 0 && this.attachments.length < event.length) {
          // console.log('New attachments detected, setting loading state');
          this.loadingAttachment = true;
        }

        // Log the update for debugging
        this.$log('update:modelValue', event);
      },
      handleFileLoaded(file) {
        // Handle when a file is loaded
        // console.log('File loaded:', file);
        // console.log('File details:', {
        //   id: file.id,
        //   serverId: file.serverId,
        //   status: file.status,
        //   filename: file.filename
        // });

        // Check if file has serverId or is fully loaded
        if (file.serverId || file.status === 'success') {
          // console.log('File upload completed, closing dialog');
          this.loadingAttachment = false;

          // Focus on textarea after upload completion
          this.$nextTick(() => {
            this.focusOnTextarea();
          });
        } else {
          // console.log('File loaded but not yet uploaded, keeping dialog open');
        }
      },
      handleFileProcessing(file) {
        // Handle when a file starts processing
        // console.log('File processing started:', file);
        this.loadingAttachment = true;
      },
      handleFileProcessed(file) {
        // Handle when a file is processed
        // console.log('File processed:', file);
        // console.log('File details:', {
        //   id: file.id,
        //   serverId: file.serverId,
        //   status: file.status,
        //   filename: file.filename
        // });

        // Check if file has serverId or is fully processed
        if (file.serverId || file.status === 'success') {
          // console.log('File upload completed, closing dialog');
          this.loadingAttachment = false;

          // Focus on textarea after upload completion
          this.$nextTick(() => {
            this.focusOnTextarea();
          });
        } else {
          // console.log('File processed but not yet uploaded, keeping dialog open');
        }
      },
      handleFileError(file) {
        // Handle when a file upload fails
        // console.log('File upload error:', file);
        this.loadingAttachment = false;
      },
      // Handle when a file is added to filepond
      handleAddFile(file) {
        // console.log('File added to filepond:', file);
        // Set loading state when file is actually added
        this.loadingAttachment = true;
      },
      // Handle when a file is removed from filepond
      handleRemoveFile(file) {
        // console.log('File removed from filepond:', file);
        // If no files remain, close the loading state
        if (this.attachments.length === 0) {
          this.loadingAttachment = false;
        }
      },
      handleFileRevert(file) {
        // Handle when a file is reverted/removed
        console.log('File reverted:', file);
      },
      handleFieldFocus() {
        // Expand textarea to 3 rows when any part of the field gets focus
        // this.textareaRows = 3;
        if(this.message.includes('\n')) {
          let count = (this.message.match(/\n/g) || []).length;
          this.textareaRows = count > this.maxMessageboxRows ? this.maxMessageboxRows : count;
        } else {
          this.textareaRows = 1;
        }
      },
      handleFieldBlur() {
        // Use setTimeout to check if any other part of the field is focused
        setTimeout(() => {
          // Check if any element within the field is still focused
          const fieldElement = this.$refs.messageBox?.$el?.closest('.v-input');
          if (fieldElement) {
            const hasFocus = fieldElement.querySelector(':focus');
            if (!hasFocus) {
              // No element in the field has focus, collapse back to 1 row
              this.textareaRows = 1;
            }
          }
        }, 100);
      },
      // Focus on textarea after file upload completion
      focusOnTextarea() {
        if (this.$refs.messageBox) {
          this.$refs.messageBox.focus();
          // console.log('Focus set on textarea after file upload completion');
        } else {
          // console.log('Textarea ref not found for focus');
        }
      }
    },
    mounted() {

      if(this.input && this.input > -1) {
        this.loadMessages();
        this.getAttachments();
        this.fetchPinnedMessages();
        if (!this.subscribeChatChannel()) {
          // Broadcast unavailable — still clear tray for this chat on open.
          this.dismissRetainableForChat();
          this.startRefreshPolling();
        }
      }

    },
    beforeUnmount() {
      this.stopRefreshPolling();
      this.unsubscribeChatChannel();
      clearTimeout(this.highlightTimer);
    }
  }
</script>

<style lang="sass">
  .v-input-chat
    .v-input-chat__messages
      margin-top: auto
      .v-input-chat__message
        padding-right: $spacer
        transition: opacity 0.7s ease
        &--loading
          opacity: 0.3
    // keep messages readable and scrollable while the chat is disabled (e.g. closed tickets)
    .v-card--disabled
      > .v-card-title,
      > .v-card-text,
      > .v-input-chat__read-only
        opacity: 1
      // messages stay interactive (links, "Show more", "Load More Messages")
      .v-infinite-scroll
        pointer-events: auto
      // except starring / pinning
      .v-input-chat__message-actions
        pointer-events: none

  // focus mode while a message is being edited: everything but the edited message and the composer fades out
  .v-input-chat--editing
    .v-input-chat__dimmable > *,
    .v-input-chat__messages--dimmed > *
      filter: blur(2px)
      opacity: 0.4
      pointer-events: none
      transition: filter 0.2s ease, opacity 0.2s ease
    .v-input-chat__dimmable,
    .v-input-chat__messages--dimmed
      cursor: pointer

  .v-input-chat__pinned
    cursor: pointer
    min-height: 56px

  .v-input-chat__pinned-indicator
    display: flex
    flex-direction: column
    gap: 2px
    align-self: stretch
    justify-content: center

  .v-input-chat__pinned-indicator-segment
    display: block
    width: 3px
    flex: 1 1 0
    max-height: 14px
    border-radius: 2px

  // a message jumped to from the pinned banner: the whole row is tinted, then fades out (like WhatsApp)
  .v-input-chat__messages--highlighted
    position: relative
    &::before
      content: ''
      position: absolute
      inset: 0
      background: rgba(var(--v-theme-primary), 0.18)
      border-radius: 8px
      pointer-events: none
      z-index: 1
      animation: v-input-chat-highlight 2.5s ease forwards

  @keyframes v-input-chat-highlight
    0%, 50%
      opacity: 1
    100%
      opacity: 0

</style>

<style lang="scss">

/* Attachment preview styling with hover delete button */
.attachment-preview-wrapper {
  position: relative;
  display: inline-block;
}

.attachment-delete-btn {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 10;
  background: rgba(255, 255, 255, 0.95) !important;
  border-radius: 0 !important;
  min-width: 10px !important;
  height: 10px !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
  opacity: 0;
  visibility: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 !important;
  margin: 0 !important;
  width: 10px !important;
  max-width: 10px !important;
  overflow: hidden !important;
  box-sizing: border-box !important;
}

.attachment-preview-wrapper:hover .attachment-delete-btn {
  opacity: 1;
  visibility: visible;
}

.attachment-delete-btn:hover {
  background: rgba(255, 255, 255, 1) !important;
  transform: scale(1.1);
}

/* Upload Modal Dialog Styling */
.upload-modal-card {
  border-radius: 20px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.upload-modal-card .v-card-text {
  padding: 32px !important;
}

</style>
