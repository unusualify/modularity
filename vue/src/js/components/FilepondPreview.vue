<template>
  <div :class="[
    'd-flex ga-2',
    showInlineFileName || showFileName ? 'flex-wrap' : ''
  ]">
    <div
      v-for="(item, index) in normalizedSource"
      :key="item.uuid"
      class="file-preview-item"
      :style="{ width: showInlineFileName || showFileName ? '100%' : 'aut' }"
    >
      <v-hover v-slot="{ isHovering, props }">
        <v-card
          v-bind="props"
          class="rounded position-relative d-flex bg-transparent"
          :width="showInlineFileName || showFileName ? '100%' : `${imageSize}`"
          :height="imageSize"
          :elevation="isHovering ? 4 : 0"
          @click="previewFile(index)"
        >
          <!-- Left side: Icon/Image -->
          <div :style="{ width: `${imageSize}px`, height: `${imageSize}px` }" class="position-relative flex-grow-0">
            <v-img
              v-if="thumbnailUrls[index]"
              :src="thumbnailUrls[index]"
              height="100%"
              width="100%"
              cover
            >
              <template v-slot:placeholder>
                <v-icon
                  :color="getFileIconColor(item.file_name)"
                  size="32"
                >
                  {{ getFileIcon(item.file_name) }}
                </v-icon>
              </template>
            </v-img>

            <v-card-text v-else class="pa-0 d-flex justify-center align-center" style="height: 100%">
              <v-icon
                :color="getFileIconColor(item.file_name)"
                size="32"
              >
                {{ getFileIcon(item.file_name) }}
              </v-icon>
            </v-card-text>
          </div>

          <!-- Right side: Filename (only if showInlineFileName is true) -->
          <div
            v-if="showInlineFileName || showFileName"
            class="d-flex align-center px-4"
          >
            <span class="text-body-2 text-truncate">{{ shortenFileName(item.file_name) }}</span>
          </div>

          <div v-if="showDate" class="ml-auto mr-1">
            <span class="text-caption text-truncate">{{ $d(new Date(item.created_at), 'numeric-full') }}</span>
          </div>

          <!-- Right side: Filename (only if showInlineFileName is true) -->
          <!-- <div
            v-if="noOverlay"
            class="d-flex align-center px-4"
          >
            <v-btn
              icon="mdi-download"
              size="x-small"
              variant="text"
              @click.stop="downloadFile(item.uuid)"
              :loading="downloadingStates[index]"
            ></v-btn>
            <v-btn
              icon="mdi-eye"
              size="x-small"
              variant="text"
              @click.stop="previewFile(index)"
              :loading="downloadingStates[index]"
            ></v-btn>
          </div> -->

          <div
            v-if="isHovering && !noOverlay"
            class="overlay-content d-flex align-center justify-center"
          >
            <v-btn
              icon="mdi-download"
              size="x-small"
              color="white"
              variant="text"
              @click.stop="downloadFile(item.uuid)"
              :loading="downloadingStates[index]"
            ></v-btn>
            <v-btn
              icon="mdi-eye"
              size="x-small"
              color="white"
              variant="text"
              @click.stop="previewFile(index)"
              :loading="downloadingStates[index]"
            ></v-btn>
          </div>
        </v-card>
      </v-hover>

      <!-- Preview Dialog -->
      <v-dialog
        v-model="previewDialogs[index]"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition"
      >
        <v-card>
          <v-toolbar dark color="primary">
            <v-btn icon dark @click="previewDialogs[index] = false">
              <v-icon color="white" icon="$close"></v-icon>
            </v-btn>
            <v-toolbar-title>{{ item.file_name }}</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>

          <v-card-text class="pa-0">
            <div class="d-flex justify-center align-center" style="height: calc(100vh - 64px)">
              <v-progress-circular
                v-if="loadingStates[index]"
                indeterminate
                color="primary"
              ></v-progress-circular>

              <v-img
                v-if="!loadingStates[index] && isImageArray[index]"
                :src="fileUrls[index]"
                contain
                max-height="90vh"
              ></v-img>

              <iframe
                v-else-if="!loadingStates[index] && fileUrls[index]"
                :src="fileUrls[index]"
                width="100%"
                height="100%"
                frameborder="0"
              ></iframe>
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        fileUrls: [],
        thumbnailUrls: [],
        isImageArray: [],
        loadingStates: [],
        downloadingStates: [],
        previewDialogs: [],
      };
    },
    props: {
      source: {
        type: [Object, Array],
        required: true,
      },
      imageSize: {
        type: [Number, String],
        default: 64
      },
      showFileName: {
        type: Boolean,
        default: false
      },
      showInlineFileName: {
        type: Boolean,
        default: false
      },
      maxFileNameLength: {
        type: Number,
        default: 10
      },
      noOverlay: {
        type: Boolean,
        default: false
      },
      showDate: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      normalizedSource() {
        return !!this.source ? Array.isArray(this.source) ? this.source : (window.__isObject(this.source) ? [this.source] : []) : [];
      },
    },
    methods: {
      processSource() {
        const length = this.normalizedSource.length;
        this.loadingStates = new Array(length).fill(false);
        this.downloadingStates = new Array(length).fill(false);
        this.previewDialogs = new Array(length).fill(false);
        this.fileUrls = new Array(length).fill(null);
        this.thumbnailUrls = new Array(length).fill(null);
        this.isImageArray = new Array(length).fill(false);

        // Generate thumbnails for images
        this.normalizedSource.forEach((item, index) => {
          if (this.isImageFile(item.file_name)) {
            this.generateThumbnail(item.uuid, index);
          }
        });
      },
      isImageFile(fileName) {
        const ext = fileName.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
      },
      async generateThumbnail(uuid, index) {
        try {
          const response = await fetch(`/api/filepond/preview/${uuid}`);
          if (!response.ok) throw new Error('Failed to fetch thumbnail');

          const blob = await response.blob();
          if (blob.type.startsWith('image/')) {
            this.thumbnailUrls[index] = URL.createObjectURL(blob);
          }
        } catch (error) {
          console.error('Error generating thumbnail:', error);
        }
      },
      getFileIcon(fileName) {
        const ext = fileName.split('.').pop().toLowerCase();
        switch (ext) {
          case 'pdf':
            return 'mdi-file-pdf-box';
          case 'doc':
          case 'docx':
            return 'mdi-file-word';
          case 'xls':
          case 'xlsx':
            return 'mdi-file-excel';
          case 'jpg':
          case 'jpeg':
          case 'png':
          case 'gif':
            return 'mdi-file-image';
          default:
            return 'mdi-file-document-outline';
        }
      },
      getFileIconColor(fileName) {
        const ext = fileName.split('.').pop().toLowerCase();
        switch (ext) {
          case 'pdf':
            return 'red';
          case 'doc':
          case 'docx':
            return 'blue';
          case 'xls':
          case 'xlsx':
            return 'green';
          case 'jpg':
          case 'jpeg':
          case 'png':
          case 'gif':
            return 'purple';
          default:
            return 'grey';
        }
      },
      async previewFile(index) {
        this.previewDialogs[index] = true;
        if (!this.fileUrls[index]) {
          this.loadingStates[index] = true;
          await this.fetchFile(this.normalizedSource[index].uuid, index);
        }
      },
      async downloadFile(uuid) {
        const index = this.normalizedSource.findIndex(item => item.uuid === uuid);
        this.downloadingStates[index] = true;

        try {
          const response = await fetch(`/api/filepond/preview/${uuid}`);
          if (!response.ok) throw new Error('Download failed');

          const blob = await response.blob();
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = this.normalizedSource[index].file_name;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          a.remove();
        } catch (error) {
          console.error('Error downloading file:', error);
        } finally {
          this.downloadingStates[index] = false;
        }
      },
      async fetchFile(uuid, index) {
        try {
          const response = await fetch(`/api/filepond/preview/${uuid}`);
          if (!response.ok) throw new Error('Network response was not ok');

          const blob = await response.blob();
          this.isImageArray[index] = blob.type.startsWith('image/');
          this.fileUrls[index] = URL.createObjectURL(blob);
        } catch (error) {
          console.error('Error fetching file:', error);
        } finally {
          this.loadingStates[index] = false;
        }
      },
      shortenFileName(fileName) {
        if (fileName.length <= this.maxFileNameLength) {
          return fileName;
        }

        const extension = fileName.split('.').pop();
        const nameWithoutExt = fileName.slice(0, fileName.lastIndexOf('.'));

        // Keep the extension and add ellipsis
        return `${nameWithoutExt.slice(0, this.maxFileNameLength)}...${extension}`;
      },
    },
    mounted() {
      this.processSource();
    },
  };
</script>

<style scoped>
  .file-preview-item {
    cursor: pointer;
  }
  .overlay-content {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    z-index: 1;
  }
</style>
