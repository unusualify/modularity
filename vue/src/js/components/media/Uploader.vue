<template>
  <div class="uploader">
    <div class="uploader__dropzone" ref="uploaderDropzone">
      <div class="button" ref="uploaderBrowseButton">{{ $trans('uploader.upload-btn-label', 'Add new') }}</div>
      <div class="uploader__dropzone--desktop">{{ $trans('uploader.dropzone-text', 'or drop new files here') }}</div>
    </div>
  </div>
</template>

<script>
import { MEDIA_LIBRARY } from '@/store/mutations'
import qq from 'fine-uploader/lib/dnd'
import FineUploaderS3 from 'fine-uploader-wrappers/s3'
import FineUploaderAzure from 'fine-uploader-wrappers/azure'
import FineUploaderTraditional from 'fine-uploader-wrappers/traditional'
import sanitizeFilename from '@/utils/sanitizeFilename.js'

export default {
  name: 'A17Uploader',
  props: {
    // Comes from ModalMedia as `:type="currentTypeObject"`, which is briefly
    // undefined while the Vuex media-library store hydrates (Inertia v3
    // bootstrap timing). Default to `null` so the validator doesn't fire;
    // the runtime is already defensive — `uploaderConfig` uses optional
    // chaining and `initUploader` bails when `uploaderConfig` is falsy, with
    // a `type` watcher that re-runs init once a real object arrives.
    type: {
      type: Object,
      default: null
    }
  },
  data: function () {
    return {
      loadingMedias: []
    }
  },
  computed: {
    uploaderConfig: function () {
      // `type` is passed in from ModalMedia as `currentTypeObject`, which is
      // undefined while the Vuex media-library store is still hydrating
      // (Inertia v3 page-bootstrap timing). Return undefined and let
      // initUploader bail; the `type` watcher re-invokes init once the
      // store fills in.
      return this.type?.uploaderConfig
    },
    uploaderValidation: function () {
      const extensions = this.uploaderConfig?.allowedExtensions || []
      return {
        allowedExtensions: extensions,
        acceptFiles: extensions.length ? '.' + extensions.join(', .') : '',
        stopOnFirstInvalidFile: false
      }
    }
  },
  methods: {
    initUploader: function () {
      // Guard: `type` may not be hydrated yet on first mounted() tick. The
      // `uploaderConfig` watcher re-invokes this once the store fills in.
      if (!this.uploaderConfig) {
        // Surface this — otherwise the "Add new" button is just an inert
        // <div> (Fine Uploader mounts a hidden <input> on top of that
        // element only after init succeeds), and clicks silently no-op.
        console.warn('[Uploader] init skipped: uploaderConfig not ready', {
          hasType: !!this.type,
          typeValue: this.type?.value
        })
        return
      }
      const buttonEl = this.$refs.uploaderBrowseButton
      const sharedConfig = {
        debug: true,
        maxConnections: 5,
        button: buttonEl,
        retry: {
          enableAuto: false
        },
        callbacks: {
          onSubmit: this._onSubmitCallback.bind(this),
          onProgress: this._onProgressCallback.bind(this),
          onError: this._onErrorCallback.bind(this),
          onComplete: this._onCompleteCallback.bind(this),
          onAllComplete: this._onAllCompleteCallback.bind(this),
          onStatusChange: this._onStatusChangeCallback.bind(this),
          onTotalProgress: this._onTotalProgressCallback.bind(this)
        },
        text: {
          fileInputTitle: 'Browse...'
        },
        messages: {
          // Todo: need to translate this in uploaderConfig
          retryFailTooManyItemsError: 'Retry failed - you have reached your file limit.',
          sizeError: '{file} is too large, maximum file size is {sizeLimit}.',
          tooManyItemsError: 'Too many items ({netItems}) would be uploaded. Item limit is {itemLimit}.',
          typeError: '{file} has an invalid extension. Valid extension(s): {extensions}.'
        }
      }

      this._uploader = this.uploaderConfig.endpointType === 's3'
        ? new FineUploaderS3({
          options: {
            ...sharedConfig,
            validation: {
              ...this.uploaderValidation
            },
            objectProperties: {
              key: id => {
                return this.unique_folder_name + '/' + sanitizeFilename(this._uploader.methods.getName(id))
              },
              region: this.uploaderConfig.endpointRegion,
              bucket: this.uploaderConfig.endpointBucket,
              acl: this.uploaderConfig.acl
            },
            request: {
              endpoint: this.uploaderConfig.endpoint,
              accessKey: this.uploaderConfig.accessKey
            },
            signature: {
              endpoint: this.uploaderConfig.signatureEndpoint,
              version: 4,
              customHeaders: {
                'X-CSRF-TOKEN': this.uploaderConfig.csrfToken
              }
            },
            uploadSuccess: {
              endpoint: this.uploaderConfig.successEndpoint,
              customHeaders: {
                'X-CSRF-TOKEN': this.uploaderConfig.csrfToken
              }
            }
          }
        })
        : this.uploaderConfig.endpointType === 'azure'
          ? new FineUploaderAzure({
            options: {
              ...sharedConfig,
              validation: {
                ...this.uploaderValidation
              },
              cors: {
                expected: true,
                sendCredentials: true
              },
              blobProperties: {
                name: id => {
                  return new Promise((resolve) => {
                    resolve(this.unique_folder_name + '/' + sanitizeFilename(this._uploader.methods.getName(id)))
                  })
                }
              },
              request: {
                endpoint: this.uploaderConfig.endpoint
              },
              signature: {
                endpoint: this.uploaderConfig.signatureEndpoint,
                version: 4,
                customHeaders: {
                  'X-CSRF-TOKEN': this.uploaderConfig.csrfToken
                }
              },
              uploadSuccess: {
                endpoint: this.uploaderConfig.successEndpoint,
                customHeaders: {
                  'X-CSRF-TOKEN': this.uploaderConfig.csrfToken
                }
              }
            }
          })
          : new FineUploaderTraditional({
            options: {
              ...sharedConfig,
              validation: {
                ...this.uploaderValidation,
                sizeLimit: this.uploaderConfig.filesizeLimit * 1048576 // mb to bytes
              },
              request: {
                endpoint: this.uploaderConfig.endpoint,
                customHeaders: {
                  'X-CSRF-TOKEN': this.uploaderConfig.csrfToken
                }
              }
            }
          })

      // Flush any files dropped before the store finished hydrating. The
      // dropzone fires `processingDroppedFilesComplete` regardless of init
      // state, so we buffer in `_onProcessingDroppedFilesComplete` and add
      // them here once an uploader instance actually exists.
      if (this._pendingDropFiles && this._pendingDropFiles.length) {
        const pending = this._pendingDropFiles
        this._pendingDropFiles = []
        this._uploader.methods.addFiles(pending)
      }
    },
    replaceMedia: function (id) {
      this.media_to_replace_id = id
      const qqinputs = this.$refs.uploaderBrowseButton.querySelectorAll('[name = "qqfile"]')
      qqinputs[Array.from(qqinputs).length - 1].click()
    },
    loadingProgress: function (media) {
      this.$store.commit(MEDIA_LIBRARY.PROGRESS_UPLOAD_MEDIA, media)
    },
    loadingFinished: function (loadingMedia, savedMedia) {
      // add the saved image to the main image list
      this.$emit('loaded', savedMedia)
      this.$store.commit(MEDIA_LIBRARY.DONE_UPLOAD_MEDIA, loadingMedia)
    },
    loadingError: function (media) {
      this.$store.commit(MEDIA_LIBRARY.ERROR_UPLOAD_MEDIA, media)
    },
    uploadProgress: function (uploadProgress) {
      this.$store.commit(MEDIA_LIBRARY.PROGRESS_UPLOAD, uploadProgress)
    },
    _onCompleteCallback (id, name, responseJSON, xhr) {
      const index = this.loadingMedias.findIndex((m) => m.id === this._uploader.methods.getUuid(id))

      if (responseJSON.success) {
        this.loadingFinished(this.loadingMedias[index], responseJSON.media)
      } else {
        this.loadingError(this.loadingMedias[index])
      }
    },
    _onAllCompleteCallback (succeeded, failed) {
      // reset folder name for next upload session
      this.unique_folder_name = null
      this.uploadProgress(0)
    },
    _onSubmitCallback (id, name) {
      this.$emit('clear')
      // each upload session will add upload files with original filenames in a folder named using a uuid
      this.unique_folder_name = this.unique_folder_name || (this.uploaderConfig.endpointRoot + qq.getUniqueId())
      this._uploader.methods.setParams({
        unique_folder_name: this.unique_folder_name,
        media_to_replace_id: this.media_to_replace_id
      }, id)

      // determine the image dimensions and add it to params sent on upload success
      const imageUrl = URL.createObjectURL(this._uploader.methods.getFile(id))
      const img = new Image()

      img.onload = () => {
        this._uploader.methods.setParams({
          width: img.width,
          height: img.height,
          unique_folder_name: this.unique_folder_name,
          media_to_replace_id: this.media_to_replace_id
        }, id)
        this.media_to_replace_id = null
      }

      img.src = imageUrl

      const media = {
        id: this._uploader.methods.getUuid(id),
        name: sanitizeFilename(name),
        progress: 0,
        error: false,
        errorMessage: null,
        isReplacement: !!this.media_to_replace_id,
        replacementId: this.media_to_replace_id
      }

      if (this.type.value === 'file') {
        this.media_to_replace_id = null
      }

      this.loadingMedias.push(media)
      this.loadingProgress(media)
    },
    _onProgressCallback (id, name, uploadedBytes, totalBytes) {
      const index = this.loadingMedias.findIndex((m) => m.id === this._uploader.methods.getUuid(id))

      if (index >= 0) {
        const media = this.loadingMedias[index]
        media.progress = uploadedBytes / totalBytes * 100 || 0
        media.error = false
        this.loadingProgress(media)
      }
    },
    _onErrorCallback (id, name, errorReason, xhr) {
      const index = id ? this.loadingMedias.findIndex((m) => m.id === this._uploader.methods.getUuid(id)) : -1

      if (index >= 0) {
        this.loadingMedias[index].errorMessage = errorReason
        this.loadingError(this.loadingMedias[index])
      } else {
        const media = {
          id: id ? this._uploader.methods.getUuid(id) : Math.floor(Math.random() * 1000),
          name: sanitizeFilename(name),
          progress: 0,
          error: true,
          errorMessage: errorReason
        }

        this.loadingMedias.push(media)
        this.loadingProgress(media)
        this.loadingError(this.loadingMedias[this.loadingMedias.length - 1])
      }
    },
    _onStatusChangeCallback (id, oldStatus, newStatus) {
      if (newStatus === 'retrying upload') {
        const index = this.loadingMedias.findIndex(function (m) {
          return m.id === id
        })

        if (index >= 0) {
          const media = this.loadingMedias[index]
          media.progress = 0
          media.error = false
          this.loadingProgress(media)
        }
      }
    },
    _onTotalProgressCallback (totalUploadedBytes, totalBytes) {
      const uploadProgress = Math.floor(totalUploadedBytes / totalBytes * 100)
      this.uploadProgress(uploadProgress)
    },
    _onDropError (errorCode, errorData) {
      console.error(errorCode, errorData)
    },
    _onProcessingDroppedFilesComplete (files) {
      // The dropzone is wired up in `mounted()` regardless of whether
      // `initUploader` succeeded — `uploaderConfig` may still be falsy on
      // the first tick (Vuex media-library store hydrating). If a user
      // drops files in that window, `_uploader` is undefined. Try to init
      // now (covers late hydration); if the uploader still isn't ready,
      // queue the files so they get added by `initUploader` once it runs
      // via the `type` watcher.
      if (!this._uploader) {
        this.initUploader()
      }
      if (!this._uploader) {
        this._pendingDropFiles = (this._pendingDropFiles || []).concat(files)
        return
      }
      this._uploader.methods.addFiles(files)
    }
  },
  watch: {
    // Re-run init whenever the prerequisite for init becomes available or
    // changes. Watching `uploaderConfig` (not just `type`) is important:
    // `type` can already be a truthy object at mount while
    // `type.uploaderConfig` is still hydrating from the Vuex store. In
    // that scenario watching `type` would never refire (same reference)
    // and the "Add new" button would stay inert because Fine Uploader
    // mounts its hidden <input> only after init succeeds.
    uploaderConfig: function (newConfig) {
      if (newConfig) {
        this.initUploader()
      }
    }
  },
  mounted () {
    // Init uploader
    this.initUploader()

    // Init dropzone
    const dropzoneEl = this.$refs.uploaderDropzone
    this._qqDropzone && this._qqDropzone.dispose()
    this._qqDropzone = new qq.DragAndDrop({
      dropZoneElements: [dropzoneEl],
      allowMultipleItems: true,
      callbacks: {
        dropError: this._onDropError.bind(this),
        processingDroppedFilesComplete: this._onProcessingDroppedFilesComplete.bind(this)
      }
    })
  },
  beforeUnmount () {
    this._qqDropzone && this._qqDropzone.dispose()
  }
}
</script>

<style lang="scss" scoped>

  $height_small_btn: 35px;

  .uploader {
    margin: 10px;
  }

  .uploader__dropzone {
    border: 1px dashed $color__border--hover;
    text-align: center;
    padding: 26px 0;
    color: $color__text--light;

    .button {
      @include btn-reset;
      display: inline-block;
      height: $height_small_btn;
      margin-right: 10px;
      line-height: $height_small_btn - 2px;
      border-radius: calc($height_small_btn / 2);
      background-color: transparent;
      border: 1px solid $color__border--hover;
      color: $color__text--light;
      padding: 0 20px;
      text-align: center;
      transition: color .2s linear, border-color .2s linear, background-color .2s linear;

      &.qq-upload-button-hover,
      &:hover {
        border-color: $color__text;
        color: $color__text;
      }

      &.qq-upload-button-focus,
      &:focus {
        border-color: $color__text;
        color: $color__text;
      }

      &:disabled {
        opacity: .5;
        pointer-events: none;
      }
    }
  }

  .uploader__dropzone--desktop {
    display: inline-block;
    vertical-align: top;
    margin-top: 8px;
    @include breakpoint(small-) {
      display: none;
    }
  }
</style>
