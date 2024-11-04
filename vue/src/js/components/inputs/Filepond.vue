<template>
  <v-input v-model="input"
    hide-details="auto">
    <template v-slot:default="defaultSlot">
      <div class="v-field v-field--active v-field--center-affix v-field--dirty v-field--variant-outlined v-locale--is-ltr">
        <div class="v-field__field d-flex flex-column" data-no-activator="">
          <v-label v-if="label">{{ label }}</v-label>
          <div class="fileField">
            <FilePond
              ref="pond"
              :id="key"
              :key="key"
              v-bind="$bindAttributes()"
              :name="name"
              @init="init"
              :files="files"
              @processfile="postProcess"
              @removefile="removeFile"
              :server="server"
            />
          </div>
        </div>
      </div>
    </template>

  </v-input>
</template>

<script>
import vueFilePond, { setOptions } from "vue-filepond";
import { useInput, makeInputProps } from '@/hooks';
import { isArray } from "lodash-es";
// Import image preview and file type validation plugins
import FilePondPluginImagePreview from "filepond-plugin-image-preview";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
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
  data() {
    return {
      files: isArray(this.modelValue) ? this.modelValue.map(function (file) {
          return {
            // source:  file.source ?? `${file.folder_name}`,
            source:  file.source ?? `${this.endPoints.load}${file.folder_name}`,
            options: {
              type : `${file.type ?? 'local'}`,
              // file initial metadata
              // metadata: {
              //     date: '2018-10-5T12:00',
              // },
            }
          }
        }) : []
    }
  },
  methods:{
    postProcess : function(error, file){
      if(!error){
        this.input = this.input.concat({
          folder_name: file.serverId,
          file_name: file.filename,
          source: `${this.endPoints.load}${file.serverId}`
        });
      }else{
        __log('postProcess error', error)
      }
    },
    removeFile: function(error, file) {
      const uuid = file.serverId?.replace(`/${file.filename}`, '') ?? file.folder_name;

      this.input = this.input.filter((asset) => asset.folder_name != uuid)
    },
    init() {
      // const files = isArray(this.modelValue) ? this.modelValue.map(function (file) {
      //       return {
      //         source:  file.source ?? `${file.folder_name}/${file.file_name}`,
      //         options: {
      //           type : `${file.type ?? 'local'}`,

      //           // file initial metadata
      //           // metadata: {
      //           //     date: '2018-10-5T12:00',
      //           // },
      //         }
      //       }
      //   }) : [];
        // __log(
        //   this.modelValue,
        //   files
        // )
      // __log('init')
      setOptions({

        // files: files,
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
    }
  },
  computed:{
    server() {
      return {
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
                    __log('onUpload', e)
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
        revert: this.endPoints.revert,
        load_: this.endPoints.load,
        load: (source, load, error, progress, abort, headers) => {
            // Should request a file object from the server here
            // ...

            // // Should call the progress method to update the progress to 100% before calling load
            // // (endlessMode, loadedSize, totalSize)
            // progress(true, 0, 1024);
            __log('load', source)
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
    // csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
        return this.modelValue?.[0]?.id + '-pod'
      }
    },
    name(){
      let name = this.$attrs.name
      if(this.obj.schema.parentName){
        name = `${this.obj.schema.parentName}.${name}`
      }
      // __log(name)
      return name
    },

  },
  created() {
    // __log(this.endPoints)
  }
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
  // padding: 10px;
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
