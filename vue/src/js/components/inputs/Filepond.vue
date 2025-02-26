<template>
  <slot name="activator" v-bind="{
    browse,
    addFile,
    removeFile,
    removeFiles,
    getFiles,
    getFile,
  }"/>
  <!-- <v-btn @click="pondBrowse">Text</v-btn> -->
  <v-input
    ref="VInput"
    v-model="input"
    hide-details="auto"
    :class="class"
  >
    <template v-slot:default="defaultSlot">
      <div
        :class="[
          'w-100',
          $slots.activator ? 'd-non' : ''
        ]"
        >

        <ue-title v-if="label" transform="none" padding="a-0" :weight="labelWeight" color="grey-darken-5">
          <slot name="label" v-bind="{
            label: label,
            ref: $refs.VInput,
          }">
            {{ label }}
          </slot>
        </ue-title>
        <ue-title v-if="subtitle" type="caption" transform="none" padding="a-0" :weight="subtitleWeight" color="grey-darken-5">
          <slot name="subtitle" v-bind="{
            subtitle: subtitle,
            ref: $refs.VInput,
          }">
            <span v-html="subtitle"></span>
          </slot>
        </ue-title>

        <slot name="body">
          <div
            :class="[
              'fileField',
              $slots.activator ? 'd-none' : ''
            ]"
          >
            <FilePond
              ref="pond"
              :id="key"
              :key="key"
              v-bind="$lodash.omit($bindAttributes(), ['rules'])"

              :allow-multiple="true"
              :allow-file-type-validation="true"
              :accepted-file-types="acceptedFileTypes"
              :max-files="maxFiles"
              :name="name"

              :files="files"
              :server="server"
              @processfile="postProcessFilepond"
              @removefile="removeFilepond"

              @init="init"
            />
          </div>
        </slot>
        <div class="hint-container">
          <ue-title v-if="hint" type="caption" transform="none" padding="a-0" :weight="hintWeight" color="grey-darken-5">
            <slot name="hint" v-bind="{
              hint: hint,
            }">
              <span v-html="hint"></span>
            </slot>
          </ue-title>
        </div>
      </div>
    </template>
  </v-input>
</template>

<script>
  import { isArray } from "lodash-es";

  import vueFilePond, { setOptions } from "vue-filepond";
  // Import image preview and file type validation plugins
  import FilePondPluginImagePreview from "filepond-plugin-image-preview";
  import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
  import "filepond/dist/filepond.min.css";
  import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";

  import { useInput, makeInputProps } from '@/hooks';
  import { globalError } from '@/utils/errors'

  // Create component
  const Component = 'Filepond'
  const FilePond = vueFilePond(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,

  );

  export default {
    name: "v-input-filepond",
    components: {
      FilePond,
    },
    props: {
      ...makeInputProps(),
      hint: {
        type: String,
        default: null,
      },
      hintWeight: {
        type: String,
        default: 'thin',
      },
      maxFiles: {
        type: Number,
        default: 2,
      },
      endPoints: {
        type: Object,
        default: () => ({}),
      },
      class: {
        type: String,
        default: '',
      },
      labelWeight: {
        type: String,
        default: 'regular',
      },
      subtitle: {
        type: String,
        default: null,
      },
      subtitleWeight: {
        type: String,
        default: 'thin',
      },
      acceptedFileTypes: {
        type: String,
        default: '',
      },
    },
    setup(props, context) {
      return {
        ...useInput(props,context),
      };
    },
    data() {
      return {
        files: isArray(this.modelValue) ? this.modelValue.map(function (file) {
            return {
              source:  file.source ?? `${this.endPoints.load}${file.uuid}`,
              options: {
                type : `${file.type ?? 'local'}`,
                // file initial metadata
                // metadata: {
                //     date: '2018-10-5T12:00',
                // },
              }
            }
          }) : [],
      }
    },
    methods:{
      postProcessFilepond : function(error, file){
        if(!error){
          this.input = this.input.concat({
            uuid: file.serverId,
            file_name: file.filename,
            source: `${this.endPoints.load}${file.serverId}`
          });
        }else{
          __log('postProcess error', error)
        }
      },
      removeFilepond: function(error, file) {
        const uuid = file.filename ?? file.serverId?.replace(`/${file.filename}`, '') ?? file.uuid;

        const newInput = this.input.filter((asset) => asset.uuid != uuid);

        this.input = newInput
      },
      init() {
        // const files = isArray(this.input) ? this.input.map(function (file) {
        //       return {
        //         source:  file.source ?? `${file.uuid}/${file.file_name}`,
        //         options: {
        //           type : `${file.type ?? 'local'}`,

        //           // file initial metadata
        //           // metadata: {
        //           //     date: '2018-10-5T12:00',
        //           // },
        //         }
        //       }
        //   }) : [];

        setOptions({

          // files_: files,
          server_: {
            process_: this.endPoints.process,
            revert_: this.endPoints.revert,
            load_: this.endPoints.load,
          },
          // callbacks

          // onaddfilestart(file) {}, // started file load
          // onaddfileprogress(file, progress)	{}, // Made progress loading a file
          // onaddfile(error, file) {}, //	If no error, file has been succesfully loaded
          // onprocessfilestart(file) {}, //	Started processing a file
          // onprocessfileprogress(file, progress) {}, //	Made progress processing a file
          // onprocessfileabort(file) {}, //	Aborted processing of a file
          // onprocessfilerevert(file) {}, //	Processing of a file has been reverted
          // onprocessfile(error, file) {
          //   __log('onprocessfile', self, file)
          //   self.input = self.input.concat({
          //     folder_name: file.serverId,
          //     file_name: file.filename,
          //   });
          // }, //	If no error, Processing of a file has been completed
          // onprocessfiles() {}, //	Called when all files in the list have been processed
          // onremovefile(error, file) {
          //   const uuid = file.serverId?.replace(`/${file.filename}`, '') ?? file.folder_name;

          //   self.input = self.input.filter((asset) => asset.folder_name != uuid)
          // }, //	File has been removed.
          // onpreparefile(file, output) {}, //	File has been transformed by the transform plugin or another plugin subscribing to the prepare_output filter. It receives the file item and the output data.
          // onupdatefiles(files) {}, //	A file has been added or removed, receives a list of file items
          // onactivatefile(file) {}, //	Called when a file is clicked or tapped
          // onreorderfiles(files, origin, target) {}, //	Called when the files list has been reordered, receives current list of files (reordered) plus file origin and target index.
          // // hooks
          // beforeDropFile(file) {	//FilePond is about to allow this item to be dropped, it can be a URL or a File object. Return true or false depending on if you want to allow the item to be dropped.
          //   return true
          // },
          // beforeAddFile(item) {//FilePond is about to add this file, return false to prevent adding it, or return a Promise and resolve with true or false.
          //   return true
          // },
          // beforeRemoveFile(item) { //	FilePond is about to remove this file, return false to prevent removal, or return a Promise and resolve with true or false.
          //   return true
          // }
        });
      },


      addFile(...args){
        return this.$refs.pond.addFile(...args)
      },
      browse(...args){
        return this.$refs.pond._pond.browse()
      },
      removeFile(...args){
        return this.$refs.pond.removeFile(...args)
      },
      removeFiles(...args){
        return this.$refs.pond.removeFiles(...args)
      },
      getFiles(...args){
        return this.$refs.pond.getFiles(...args)
      },
      getFile(...args){
        return this.$refs.pond.getFile(...args)
      },
    },
    computed:{
      server() {
        return {
          // revert: this.endPoints.revert,
          revert: (uniqueFileId, load, error) => {
            let requestId = 'revert-' + uniqueFileId
            axios.delete(this.endPoints.revert, {
              requestId,
              data: uniqueFileId
            }).then(res => {
              this.input = this.input.filter(file => file.uuid != uniqueFileId)
              load()
            }).catch(err => {
              console.error('server revert error', err)
            })
          },
          process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
            // fieldName is the name of the input field
            // file is the actual file object to send
            const formData = new FormData();
            if(fieldName.match(/(\w)+.(\w)+/)){
              let parts = fieldName.split('.')
              fieldName = ''
              for(const index in parts){
                if(index == 0) fieldName += parts[index]
                else fieldName += `[${parts[index]}]`
              }
              // fieldName = `${parentName}[${fieldName}]`
            }
            formData.append(fieldName, file, file.name);
            let requestId = 'process-' + fieldName
            axios.post( this.endPoints.process, formData, {
                requestId,
                onUploadProgress: e => {
                  // __log('onUpload', e)
                  // Should call the progress method to update the progress to 100% before calling load
                  // Setting computable to false switches the loading indicator to infinite mode
                  progress(e.lengthComputable, e.loaded, e.total);
                }
              })
              .then(function (res) {
                //handle success
                load(res.data);
              })
              .catch(function (res) {
                if(res.status == 500 && res.response.match(/^\<script\> Sfdump/)){
                    globalError(Component, {
                      message: 'Filepond process error.',
                      value: request
                    })
                    error('Dump error on processing');
                  }else{
                    error('Processing Error');
                  }
              });

            // Should expose an abort method so the request can be cancelled
            return {
                abort: () => {
                  __log('aborted')
                  // This function is entered if the user has tapped the cancel button
                  // request.abort();
                  axios.abort(requestId);

                  // Let FilePond know the request has been cancelled
                  abort();
                },
            };
          },

          // load_: this.endPoints.load,
          load: (source, load, error, progress, abort, headers) => {
              // Should request a file object from the server here
              // ...

              // // Should call the progress method to update the progress to 100% before calling load
              // // (endlessMode, loadedSize, totalSize)
              // progress(true, 0, 1024);

              axios.get(source,{
                  responseType: "blob",
                  validateStatus: status => (status >= 200 && status < 300) || status === 422
                })
                .then((res) => {
                  if (res.status == 200) {
                    load(res.data)
                  }else if (res.status == 500) {
                    throw new Error("Answer not found")
                  }else {
                    throw new Error("Some other status code")
                  }
                })
                .catch(function (error) {
                  console.error(error);
                });


              // request.send()
              // // Can call the header method to supply FilePond with early response header string
              // // https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/getAllResponseHeaders
              // // headers(headersString);

              // Should expose an abort method so the request can be cancelled
              return {
                  abort: () => {
                      // User tapped cancel, abort our ongoing actions here

                      // Let FilePond know the request has been cancelled
                      abort();
                  },
              };
          },
          headers: {
            'X-Csrf-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
        }
      },
      input: {
        get(){
          return this.modelValue ?? []
        },
        set(val){
          this.updateModelValue(val)
        }
      },
      key: {
        get(){
          return `filepond-${Date.now()}`
        }
      },
      name(){
        let name = this.$attrs.name
        if(this.obj && this.obj.schema && this.obj.schema.parentName){
          name = `${this.obj.schema.parentName}.${name}`
        }
        // __log(name)
        return name
      },

    },
    created() {
    },

  };
</script>

<style lang="scss" scoped>
  .fileField {
    width: 100%;
    display: block;
    border-radius: 4px;
    overflow-x: hidden;

    &:hover {
      color: rgba(var(--v-theme-primary), 0.9) !important;
      :deep(.filepond--panel-root) {
        border-width: 2px;
        border-color: rgba(var(--v-theme-primary), 1);
      }
    }

    // Add error state styling
    &:has(:deep([data-filepond-item-state*='error'])),
    &:has(:deep(> [data-filepond-item-state*='invalid'])),
    &:has(:deep(.filepond--panel-root[data-status='error'])) {
      :deep(.filepond--panel-root) {
        border-width: 2px;
        border-color: rgba(var(--v-theme-error), 1);
        background-color: rgba(var(--v-theme-error), 0.05);
      }
    }
  }

  :deep(.filepond--panel-root) {
    background-color: white;
    border: 1px solid #ccc;
  }

  :deep(.filepond--drop-label) {
    color: #666;
    font-size: 14px;
  }

  :deep(.filepond--item-panel) {
    background-color: rgba(var(--v-theme-success), 1);
  }

  /* :deep is a special selector in Vue's scoped styles that allows you to target child components' styles, bypassing the encapsulation of scoped styles. This is useful for styling elements that are not directly part of the current component's template. */
  :deep(.filepond--panel-root[data-status='error'])
  {
    background-color: rgba(var(--v-theme-error), 1);
  }

  :deep([data-filepond-item-state*=invalid], [data-filepond-item-state*=error]){
    .filepond--item-panel{
      background-color: rgba(var(--v-theme-error), 1);
    }
  }

  :deep(.filepond--file-status) {
    color: rgba(var(--v-theme-on-primary), 0.9);
  }

  :deep(.filepond--browse) {
    color: rgba(var(--v-theme-primary), 1);
    text-decoration: none;
    cursor: pointer;

    &:hover {
      text-decoration: underline;
    }
  }

  :deep(.filepond--progress-indicator) {
    background-color: rgba(33, 150, 243, 0.15);
  }

  :deep(.filepond--file-action-button) {
    background-color: rgba(0, 0, 0, 0.5);
    color: rgba(var(--v-theme-on-primary), 1);

    &:hover {
      background-color: rgba(0, 0, 0, 0.75);
    }
  }

  .hint-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: static !important;
    transform: translateY(-15px) translateX(0px);
  }

</style>
