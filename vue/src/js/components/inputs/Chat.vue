<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="v-input-chat"
    >
    <template v-slot:default="defaultSlot">
      <v-card class='w-100' :elevation="elevation"
        :class="[
          !noBackground ? `bg-${background}` : '',
          disabled ? 'bg-grey-lighten-4' : ''
        ]"
        :heightx="height"
        :disabled="disabled"
      >
        <v-card-title class="d-flex w-100 py-4">
          <ue-title :text="label" align="center" padding="a-0" transform="none" class="w-100">
            <template v-slot:right>
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
                  <v-dialog
                    v-if="uploadedAttachments.length > 0"
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
        </v-card-title>

        <v-card-text class="">
          <v-infinite-scroll
            ref="infiniteScroll"
            :height="bodyHeight ? `calc(${bodyHeight})` : `calc(${height}*0.65)`"
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
                  :message="message"
                  :reverse="message.reverse"
                  :reversex="index % 2 === 0"
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

        <v-card-actions class="pa-4" v-if="!noSendAction">

          <slot name="sending">
            <v-text-field
              v-model="message"
              variant="solo"
              :disabled="loading"
              hide-details
              placeholder="Type your answer here..."
              density="compact"
              @click:append="sendMessage"
              @keyup.enter="sendMessage"
            >
              <template v-slot:append>
                <v-btn variant="elevated" density="comfortable" @click="sendMessage" :disabled="loading || !message">
                  {{ $t('Send') }}
                </v-btn>
                <v-input-filepond
                  v-if="filepond"
                  ref="inputFilepond"
                  class="d-non"
                  v-bind="invokeRule($lodash.omit(filepond, ['type']))"
                  v-model="attachments"

                  :xmodelValue="attachments"
                  @xupdate:modelValue="$log('update:modelValue', $event)"
                >
                  <template v-slot:activator="activatorProps">
                    <!-- <v-btn
                      size="default"
                      icon="mdi-paperclip"
                      density="compact"
                      @click="activatorProps.browse()"
                      :disabled="loading"
                      >
                    </v-btn> -->
                  </template>
                </v-input-filepond>
              </template>
              <template v-if="filepond" v-slot:append-inner>
                <v-btn size="default" icon="mdi-paperclip" density="compact" @click="$refs.inputFilepond.browse()" />
                <ue-filepond-preview :source="attachments" image-size="24"/>
                <template v-for="attachment in attachments">

                </template>
              </template>
            </v-text-field>
          </slot>
        </v-card-actions>
      </v-card>
    </template>
  </v-input>
</template>

<script>
  import { useInput, makeInputProps, makeInputEmits, useValidation } from '@/hooks'
  import ChatMessage from '@/components/others/ChatMessage.vue';
  export default {
    name: 'v-input-chat',
    emits: [...makeInputEmits],
    components: {
      ChatMessage
    },
    props: {
      ...makeInputProps(),
      endpoints: {
        type: Object,
        default: () => ({})
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
        default: 'grey-lighten-5'
      },
      elevation: {
        type: Number,
        default: 2
      },
      density: {
        type: String,
        default: 'comfortable'
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
        page: this.initialPage,
        lastPage: 1,
        dialogOpen: false,

        attachments: [],
        uploadedAttachments: [],
        uploadedAttachmentsDialog: false,

        refreshInterval: null
      }
    },
    computed: {
      currentUser() {
        // __log(this.$store.getters.userProfile)
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
      }
    },
    watch: {

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
            __log(response.data)
            this.messages = this.formatMessages(response.data);
            done('empty');
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
        const index = this.findMessageByTempId(tempId);
        if (index !== -1) {
          this.messages[index] = {
            ...this.messages[index],
            ...newMessage
          };
        }
      },
      sendMessage() {
        const endpoint = this.endpoints.store.replace(':id', this.input);

        const newMessage = {
          loading: true,
          content: this.message,
          tempId: Date.now() // Add unique tempId to identify this message later
        };
        let tempId = newMessage.tempId;
        this.addMessage(newMessage);

        this.loading = true;

        axios.post(endpoint, {
          content: this.message,
          attachments: this.attachments
        }).then(response => {
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
        }).finally(() => {
          this.loading = false;
          this.scrollEndInfiniteScroll();
        });
      },
      getAttachments() {
        const endpoint = this.endpoints.attachments.replace(':id', this.input);
        axios.get(endpoint).then(response => {
          this.uploadedAttachments = response.data;
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
        });
      }
    },
    created() {
      // __log('Chat', this.getAttachments());
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

</style>
