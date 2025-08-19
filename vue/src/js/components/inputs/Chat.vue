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
          disabled ? 'bg-grey-lighten-4' : ''
        ]"
        :color="color"
        :heightx="height"
        :disabled="disabled"
      >
        <v-card-title class="w-100 py-4 text-wrap">
          <ue-title :text="label" align="center" padding="a-0" transform="none" class="w-100" type="body-1" color="grey-darken-4" weight="medium">
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

          <!-- Pinned Message -->
          <v-list v-if="pinnedMessage"
            id="PinnedMessage"
            elevation="3"
            class="bg-grey-lighten-4 mx-n4"
            style="z-index: 1000; height: 92px;"
            :items="[{
              title: pinnedMessage.content,
              subtitle: pinnedMessage.created_at ? $d(new Date(pinnedMessage.created_at), 'short') : '',
              prepend: pinnedMessage.user_profile.avatar_url
            }]"
            lines="two"
            prependIcon="mdi-pin"
            item-props
          >
            <template #title="{ title }">

              <div v-if="pinnedMessageExpanded || title.length < 20">
                <span class="text-wrap" v-html="title"></span>
                <v-btn v-if="title.length >= 20" density="compact" variant="plain" size="x-small" @click="togglePinnedMessageExpand"> {{ $t('Show less') }}</v-btn>
              </div>
              <div v-else>
                <span class="text-wrap" v-html="title.slice(0, 20)"></span>
                <span class="text-grey-darken-1">...</span>
                <v-btn variant="plain" size="small" @click="togglePinnedMessageExpand"> {{ $t('Show more') }}</v-btn>
              </div>
            </template>
            <template #prepend="prependScope">
              <v-icon size="small" color="primary" class="mr-2" style="transform: rotate(325deg)" @click="unpinMessage(pinnedMessage)">mdi-pin</v-icon>
              <v-tooltip :text="pinnedMessage.user_profile.name" location="top">
                <template #activator="{ props: activatorProps }">
                  <v-avatar :image="prependScope.item.prepend" size="24" v-bind="activatorProps"/>
                </template>
              </v-tooltip>
            </template>
          </v-list>
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
            <div v-for="(message, index) in formattedMessages" :key="index" class="v-input-chat__messages">
              <slot name="message" :message="message" :index="index">
                <ChatMessage
                  :class="[
                    'v-input-chat__message',
                    message.loading  ? 'v-input-chat__message--loading' : '',
                  ]"
                  :modelValue="message"
                  @update:modelValue="updatedMessage($event, index)"
                  :reverse="message.reverse"
                  :updateEndpoint="endpoints.update"
                  :noStarring="noStarring"
                  :noPinning="noPinning"
                  :contentTruncateLength="contentTruncateLength"
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

        <v-card-actions class="bg-surface" v-if="!noSendAction">
          <slot name="sending">
            <v-textarea
              class="position-absolute bg-surface px-4 pb-4"
              style="bottom: 0; left: 0; right: 0; z-index: 1000;"
              ref="messageBox"
              v-model="message"
              :variant="inputVariant"
              :disabled="loading"
              hide-details
              placeholder="Type here..."
              density="compact"
              @click:append="sendMessage"
              @keydown.enter="handleEnterKey"
              @keydown.tab="handleTabKey"
              @focus="handleFieldFocus"
              @blur="handleFieldBlur"
              :rows="textareaRows"
            >
              <template v-slot:prepend v-if="$vuetify.display.smAndUp">
                <div class="flex-grow-0">
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

      <!-- EMOJI COMPONENT -->
      <Emojis
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
  import Emojis from './Emojis.vue';
  export default {
    name: 'v-input-chat',
    emits: [...makeInputEmits],
    components: {
      ChatMessage,
      Emojis
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
        pinnedMessage: null,
        showEmojiPicker: false,
        pinnedMessageExpanded: false,

      }
    },
    computed: {
      currentUser() {
        return this.$store.getters.userProfile;
      },
      formattedMessages() {
        return this.formatMessages(this.messages);
      },
      noRemainingItems() {
        return !this.loading && (this.perPage > -1 && this.page === this.lastPage);
      },
      showLoadMoreButton() {
        return !this.noRemainingItems && this.perPage > -1;
      },
      isInfiniteScrollable() {
        return this.perPage > -1;
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

        if (hasAttachments || hasText) {
          return 'mdi-send-check';
        }

        return 'mdi-send';
      },
      calculatedBodyHeight() {
        let extraHeight = 0;
        let minusHeight = '';

        if(this.pinnedMessage) {
          extraHeight += this.$jquery('#PinnedMessage').outerHeight(true);
        }

        if(extraHeight > 0) {
          minusHeight = ` - ${extraHeight}px`;
        }

        return this.bodyHeight ? `calc(${this.bodyHeight}${minusHeight})` : `calc(${this.height}*0.65${minusHeight})`;
      }

    },
    watch: {
      messages: {
        handler(newValue) {
          this.pinnedMessage = newValue.find(message => message.is_pinned);

          if(!this.pinnedMessage) {
            this.fetchPinnedMessage();
          }
        },
        deep: true
      },

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
      }
    },
    methods: {
      togglePinnedMessageExpand() {
        this.pinnedMessageExpanded = !this.pinnedMessageExpanded;
      },
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

        axios.get(endpoint, { params }).then(response => {
          if(response.status === 200) {
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
      unpinMessage(message) {
        let endpoint = this.endpoints.update.replace(':id', message.id);

        let self = this;
        let index = this.messages.findIndex(m => m.id === message.id);
        axios.put(endpoint, {
          is_pinned: false
        }).then(response => {
          self.messages[index] = {
            ...self.messages[index],
            is_pinned: false
          };
        });
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
      fetchPinnedMessage() {
        const endpoint = this.endpoints.pinnedMessage.replace(':id', this.input);
        axios.get(endpoint).then(response => {

          if(response.status === 200 && response.data) {
            if(Object.keys(response.data).length > 0) {
              this.pinnedMessage = response.data;
            }
          }
        });
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
            user_id: this.currentUser.id
          }
        }).then(response => {
          if(response.status === 200) {
            if(response.data.length > 0) {
              this.messages = [...this.messages, ...response.data];
              this.getAttachments();
            }
          }

          if(response.status === 401) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
          }
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

        if(oldMessage.is_pinned !== event.is_pinned) {
          if(event.is_pinned) {
            let oldPinnedMessage = this.pinnedMessage;
            this.pinnedMessage = event;

            if(oldPinnedMessage) {
              let oldPinnedMessageIndex = messages.findIndex(message => message.id === oldPinnedMessage.id);
              if(oldPinnedMessageIndex !== -1) {
                messages[oldPinnedMessageIndex] = {
                  ...messages[oldPinnedMessageIndex],
                  is_pinned: false
                };
              }
            }

          } else {
            this.pinnedMessage = null;
          }
        }

        if(index !== -1) {
          messages[index] = {
            ...messages[index],
            ...event
          };
        }

        this.messages = messages;
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
        this.textareaRows = 3;
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
    created() {

    },
    mounted() {

      if(this.input && this.input > -1) {
        this.loadMessages();
        this.getAttachments();
        this.refreshInterval = setInterval(() => {
          this.refreshMessages();
        }, this.refreshTime);
      }

    },
    beforeUnmount() {
      clearInterval(this.refreshInterval);
      this.refreshInterval = null;
      if(this.input && this.input > -1) {
      }
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
