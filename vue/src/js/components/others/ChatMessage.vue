<template>
  <div
    :class="[
      'd-flex mb-4 w-100',
      reverse ? 'flex-row-reverse' : 'flex-row'
    ]"
    >
    <div
      style="max-width: 60%; min-width: 20%;"
      :class="[
        'd-flex bg-grey-lighten-6 elevation-2 px-4 py-3 rounded',
        reverse ? 'flex-row-reverse' : 'flex-row'
      ]"
    >
      <!-- Avatar -->
      <v-tooltip :text="formatDate(message)" location="top">
        <template v-slot:activator="{ props }">
          <v-avatar :size="avatarSize"
          :class="[
            reverse ? 'ml-3' : 'mr-3'
          ]"
          :image="message.user_profile.avatar_url" v-bind="props" />
        </template>
      </v-tooltip>
      <div
        :stylex="{ width: `calc(50% - ${avatarSize}px)` }"
        class="w-100">
        <!-- Message -->
        <div
          :class="[
            'text-grey text-caption w-100 d-flex justify-space-between',
            reverse ? 'flex-row-reverse' : 'flex-row'
          ]">
          <div>{{ message.user_profile.name }}</div>
          <!-- <div> {{ message.created_at ? $d(new Date(message.created_at), 'numeric-full') : window.$moment().fromNow() }}</div> -->
        </div>
        <div :class="['d-flex', reverse ? 'flex-row-reverse' : 'flex-row']">{{ message.content }}</div>

        <!-- Attachments -->
        <div v-if="message.attachments.length > 0" class="mt-2 pa-1 rounded" style="">
          <ue-title :text="$t('Attachments')" padding="b-2" type="caption" color="none"/>
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
</template>

<script>
  export default {
    props: {
      avatarSize: {
        type: Number,
        default: 50
      },
      message: {
        type: Object,
        required: true
      },
      reverse: {
        type: Boolean,
        default: false
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
      }
    }
  }
</script>

<style>

</style>
