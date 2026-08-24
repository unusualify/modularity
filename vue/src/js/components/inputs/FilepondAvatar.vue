<script setup>
import { ref } from 'vue';
import { useInput, makeInputProps, makeInputEmits, makeFilepondProps } from '@/hooks';

// This component renders a fragment (two sibling root elements: the avatar
// wrapper div and the v-input-filepond), so Vue can't auto-inherit fall-
// through attrs onto a single root. Combined with form-schema passing many
// Vuetify input attrs we don't declare (`placeholder`, `errorMessages`,
// `prependIcon`, etc.), this triggers the "Extraneous non-props attributes"
// warning. We disable auto-inherit and forward `$attrs` explicitly to the
// underlying filepond input below — that is the element those attrs are
// actually meant for.
defineOptions({ inheritAttrs: false });

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
  ...makeInputProps(),
  ...makeFilepondProps(),
});

const emit = defineEmits(makeInputEmits);

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
  console.log('activateLoading');
  fileLoading.value = true;
}

const deactivateLoading = () => {
  console.log('deactivateLoading');
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
      <v-progress-circular
        v-if="fileLoading"
        :size="25"
        color="success"
        indeterminate
      ></v-progress-circular>
      <v-icon :disabled="disabled" size="default">mdi-account-circle-outline</v-icon>
    </div>
  </div>
  <v-input-filepond
    v-bind="$attrs"
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

    :allow-multiple="false"
    :allow-replace="true"

    @loadingFile="activateLoading"
    @loadedFile="deactivateLoading"
  >
    <template v-slot:activator="activatorProps">

    </template>
  </v-input-filepond>
</template>

<style scoped lang="scss">
  .v-input-filepond-avatar {
    height: 0;
    max-height: 0;
    margin: 0;
    padding: 0;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
  }

  .v-input-filepond-avatar .filepond--root {
    background: transparent;
    border:    none;
    box-shadow:none;
    padding:   0;
  }

  .v-input-filepond__avatar-wrapper  {
    position: relative;
    display: inline-block;

    .v-input-filepond__edit-icon {
      position: absolute;
      bottom: 10px;
      right: 0;
      transform: translate(25%, 25%);
      background: rgba(var(--v-theme-primary), 0.5);
      border-radius: 50%;
      padding: 2px;
      box-shadow: 0 0 4px rgba(0,0,0,0.2);
      cursor: pointer;
    }
  }

</style>
