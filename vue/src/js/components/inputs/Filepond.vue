<template>
  <v-input v-model="input">
    <div class="v-field v-field--active v-field--center-affix v-field--dirty v-field--variant-outlined v-theme--jakomeet v-locale--is-ltr">
      <div class="v-field__field" data-no-activator="">
        <div class="fileField">
          <file-pond
            ref="pond"
            :key="key"
            :id="key"
            v-bind:files="files"
            v-on:init="handleFilePondInit"
            v-bind="$bindAttributes()"
            @processfile="postProcess"
            @removefile="removeFile"
          />
        </div>
      </div>
    </div>
  </v-input>
</template>

<script>
import vueFilePond, { setOptions } from "vue-filepond";
import { useInput, makeInputProps } from '@/hooks';
import "filepond/dist/filepond.min.css";


// Preview related plugins and imports
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";

// Import image preview and file type validation plugins
import FilePondPluginImagePreview from "filepond-plugin-image-preview";

// Create component
const FilePond = vueFilePond(
  FilePondPluginImagePreview
);

export default {
  name: "ue-custom-input-filepond",
  props: {
    ...makeInputProps(),
    endPoints: {
      type: Object,
      default: () => ({}),
    },
  },
  setup(props, context) {

    return {
      ...useInput(props,context),
    };
  },
  methods:{
    postProcess : function(error, file){
      this.input = this.input.concat({
        folderName: file.serverId,
        fileName: file.filename,
        source: '/tmp/' + file.serverId + '/' + file.filename
      });
    },
    removeFile: function(error, file) {
      const uuid = file.serverId.replace(`/${file.filename}`, '')
      this.input = this.input.filter((asset) => asset.folderName != uuid)
    },
    handleFilePondInit : function() {
      setOptions({
        files: this.modelValue.map(function (file) {
            return {
              source:  file.source ?? `${file.folderName}/${file.fileName}`,
              options: {
                type : `${file.type ?? 'local'}`,
              }
            }
        }),
        server: {
          process: this.endPoints.process,
          revert: this.endPoints.revert,
          load: this.endPoints.load,
          headers: {
            'X-Csrf-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
        },
      });
    }
  },
  computed:{
    csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    input: {
      get(){
        return this.modelValue ?? []
      },
      set(val){
        this.updateModalValue(val)
      }
    },
    key: {
      get(){
        return Math.ceil(Math.random()* this.modelValue.length) + '-pod'
      }
    }

  },

  components: {
    FilePond,
  },
};
</script>

<style lang="scss" scoped>
.fileField {
  width: 100%;
  display: block;
  border-radius: 2px;
  overflow-x: hidden;
}

.fileField__trigger {
  padding: 10px;
  position: relative;
  border-top: 1px solid $color__border--light;

  &:first-child {
    border-top: 0 none;
  }
}

.fileField__note {
  color: $color__text--light;
  float: right;
  position: absolute;
  bottom: 18px;
  right: 15px;
  display: none;

  @include breakpoint('small+') {
    display: inline-block;
  }

  @include breakpoint('medium') {
    display: none;
  }
}

.fileField__list {
  overflow: hidden;
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
}
</style>
