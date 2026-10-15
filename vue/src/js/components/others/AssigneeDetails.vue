<script setup>
  import { ref, computed } from 'vue'
  import {
    useValidation,
  } from '@/hooks'

  const emit = defineEmits(['update:attachments', 'update:attachmentsLoading', 'click:complete', 'click:save'])

  const props = defineProps({
    loading: {
      type: Boolean,
      default: false,
    },
    isAssignee: {
      type: Boolean,
      default: false,
    },
    isAuthorized: {
      type: Boolean,
      default: false,
    },
    assignment: {
      type: Object,
      default: () => ({}),
    },
    formattedAssignment: {
      type: Object,
      default: () => ({}),
    },
    filepond: {
      type: Object,
      default: null,
    },
    attachments: {
      type: Array,
      default: () => [],
    },
    attachmentsLoading: {
      type: Boolean,
      default: false,
    },
    wellPrint: {
      type: Boolean,
      default: false,
    },
  })

  const { invokeRule} = useValidation(props)


  const attachmentsModel = computed({
    get: () => props.attachments,
    set: (value) => {
      console.log('update:attachments', value)
      emit('update:attachments', value)
    },
  })
  const attachmentsLoadingModel = computed({
    get: () => props.attachmentsLoading,
    set: (value) => {
      emit('update:attachmentsLoading', value)
    },
  })

</script>

<template>
  <v-menu
    :close-on-content-click="false"
    location="end"
    Xopen-on-hover
  >
    <template v-slot:activator="{ props }">
      <v-list v-if="(isAssignee || isAuthorized) && formattedAssignment"
        id="assigneeList"
        :items="[{title: $t('Assignee'), prependAvatar: formattedAssignment.prependAvatar ?? '', subtitle: formattedAssignment.assigneeName}]"
        lines="three"
        item-props
        class="pa-0 v-input-assignment__list--assignee flex-grow-1 flex-shrink-0"
        color="primary"
        v-bind="props"

        variant="plain"
      >
        <template v-slot:title="{ title }">
          <div class="text-primary font-weight-bold" v-html="title"></div>
        </template>
        <template v-slot:subtitle="{ subtitle }">
          <div class="font-weight-medium" v-html="subtitle"></div>
        </template>
      </v-list>
    </template>
    <v-card min-width="300" max-width="500">

      <!-- Task Summary -->
      <v-list
        id="assigneeList"
        :items="[formattedAssignment]"
        lines="three"
        item-props
        class="pa-0 v-input-assignment__list--assignee"
      >
        <template v-slot:title="{ title }">
          <div v-html="title"></div>
        </template>
        <template v-slot:subtitle="subtitleScope">
          <div v-html="formattedAssignment.subDescription"></div>
        </template>
      </v-list>

      <v-divider></v-divider>

      <v-list lines="10">
        <!-- Last Assignment Description -->
        <v-list-item
          :title="$t('fields.description')"
          :subtitle="assignment.description"
          class=""
        >
          <template v-slot:prepend="{ isSelected, select }">
            <v-icon icon="mdi-information-outline"></v-icon>
          </template>
          <template v-if="wellPrint" v-slot:subtitle="{ subtitle }">
            <ue-well-print :text="subtitle" class="w-100" style="word-break: break-word;" />
          </template>
        </v-list-item>

        <v-list-item v-if="assignment.preliminaries && assignment.preliminaries.length > 0"
          :title="$t('fields.preliminary-documents')"
          :subtitle="assignment.preliminaries"
        >
          <template v-slot:prepend="{ isSelected, select }">
            <v-icon icon="mdi-file-document-alert-outline"></v-icon>
          </template>
          <template v-slot:subtitle="{ subtitle }">
            <ue-filepond-preview :source="assignment.preliminaries ?? []" show-inline-file-name image-size="24"/>
          </template>
        </v-list-item>

        <v-list-item
          v-if="isAuthorized && !isAssignee && assignment.attachments && assignment.attachments.length > 0"
          :title="$t('fields.files')"
          subtitle="Files subtitle"
        >
          <template v-slot:prepend="{ isSelected, select }">
            <v-icon icon="mdi-file-outline"></v-icon>
          </template>
          <template v-slot:subtitle="{ subtitle }">
            <ue-filepond-preview :source="assignment.attachments ?? []" show-inline-file-name image-size="24"/>
          </template>

        </v-list-item>
      </v-list>

      <v-card-text v-if="isAssignee">
        <v-input-filepond v-if="filepond"
          label="Files"
          ref="inputFilepond"
          v-bind="invokeRule($lodash.omit(filepond, ['type']))"
          v-model="attachmentsModel"

          @loadingFile="attachmentsLoadingModel = true"
          @loadedFile="attachmentsLoadingModel = false"
          :disabled="assignment.status !== 'pending'"
        >
        </v-input-filepond>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions v-if="isAssignee">
        <v-btn
          variant="tonal"
          color="success"
          @click="$emit('click:complete')"
          :disabled="assignment.status !== 'pending'"
        >
          {{ $t('Complete') }}
        </v-btn>

        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          variant="tonal"
          @click="attachmentsModel.length > 0 && $emit('click:save', {
            attachments: attachmentsModel
          })"
          :loading="attachmentsLoadingModel || loading"
          :disabled="attachmentsModel.length < 1 || assignment.status !== 'pending'"
        >
          {{ $t('Save') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>
