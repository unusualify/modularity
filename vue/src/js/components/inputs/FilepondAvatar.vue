<script setup>
import { ref, watch } from 'vue';
import { useInput, makeInputProps, makeInputEmits, makeFilepondProps } from '@/hooks';

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  ...makeInputProps(),
  ...makeFilepondProps(),
});

const emit = defineEmits(makeInputEmits);

const previewUrl = ref(null);

const Input = useInput(props, { emit, updateModelValue: (val, old) => {
  if (val.length > 1) {
    Input.input.value = [val[1]];
  } else {
    emit('update:modelValue', val);
  }
} });

const fileLoading = ref(false);

const FilepondRef = ref(null);

const browse = () => {
  if (fileLoading.value || props.disabled) {
    return;
  }
  FilepondRef.value.browse();
}

const activateLoading = () => {
  fileLoading.value = true;
}

const deactivateLoading = () => {
  fileLoading.value = false;
}

</script>

<template>
  <div class="v-input-filepond__avatar-wrapper" @click="browse" >
    <v-avatar
      size="90"
      :class="disabled ? '' : 'cursor-pointer'"
    >
      <v-img
        v-if="Input.input.value.length > 0"
        alt="Avatar"
        :src="Input.input.value[0].source"
      >
      </v-img>
      <v-icon
        v-else
        icon="mdi-account"
      ></v-icon>
    </v-avatar>
    <div :class="[
      'v-input-filepond__edit-icon',
      !fileLoading ? 'bg-primary-lighten-3' : 'bg-surface'
    ]">
      <!-- you can swap mdi-pencil for any edit icon you prefer -->
      <v-progress-circular
        v-if="fileLoading"
        :size="25"
        color="success"
        indeterminate
      ></v-progress-circular>
      <v-icon :disabled="disabled" size="default">mdi-account-edit-outline</v-icon>
    </div>
  </div>
  <v-input-filepond
    ref="FilepondRef"
    v-model="Input.input.value"
    class="v-input-filepond-avatar"

    :hide-details="hideDetails"
    :hint="hint"
    :min="min"
    :max-files="maxFiles"
    :rules="rules"
    :hint-weight="hintWeight"
    :end-points="endPoints"
    :label-weight="labelWeight"
    :subtitle="subtitle"
    :subtitle-weight="subtitleWeight"
    :accepted-file-types="acceptedFileTypes"

    @loadingFile="activateLoading"
    @loadedFile="deactivateLoading"
  >
    <template v-slot:activator="activatorProps">

    </template>
  </v-input-filepond>
</template>

<style scoped lang="scss">
  .v-input-filepond-avatar .filepond--root {
    background: transparent;
    border:    none;
    box-shadow:none;
    padding:   0;
  }

  /* 1) Wrap avatar in a relative container */
  .v-input-filepond__avatar-wrapper  {
    position: relative;
    display: inline-block;

    .v-input-filepond__edit-icon {
      position: absolute;
      bottom: 10px;
      right: 0;
      transform: translate(25%, 25%); /* nudge it slightly outside the circle */
      background: rgba(var(--v-theme-primary), 0.5);
      border-radius: 50%;
      padding: 2px;
      box-shadow: 0 0 4px rgba(0,0,0,0.2);
      cursor: pointer;
    }
  }

</style>
