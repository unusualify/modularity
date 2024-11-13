<template>
  <div>
    <template v-if="loading">
      <div class="d-flex flex-column align-center">
        <v-progress-circular
          indeterminate
          color="primary"
        ></v-progress-circular>
        <span class="mt-2">{{ loadingText }}</span>
      </div>
    </template>
    <!-- Loaded content -->
    <template v-else>
      <!-- For array response -->
      <template v-if="Array.isArray(values)">
        <div v-for="(item, index) in values" :key="index">
          <ue-text-display
            class="text-h5"
            :text="getDisplayText(item, index)"
            :sub-text="getSubText(item, index)"
          />
        </div>
      </template>

      <!-- For single object response -->
      <template v-else>
        <ue-text-display
          class="text-h5"
          :text="getDisplayText(values)"
          :sub-text="getSubText(values)"
        />
      </template>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    payload: {
      type: Object,
      required: true
    },
    endpoint: {
      type: String,
      required: true
    },
    printKeys: {
      type: [Array, Object],
      required: true
    },
    loadingText: {
      type: String,
      default: 'Loading...'
    }
  },
  data() {
    return {
      values: {},
      loading: true  // Add loading state
    }
  },
  methods: {
    getDisplayText(data, index = null) {
      // __log(data, index, this.printKeys);
      if (Array.isArray(this.printKeys)) {
        // If printKeys is array of strings
        if (typeof this.printKeys[index || 0] === 'string') {
          return data[this.printKeys[index || 0]];
        }
        // If printKeys is array of objects
        const key = this.printKeys[index || 0];
        return data[key?.text];
      }
      // If printKeys is single object
      return data[this.printKeys.text];
    },

    getSubText(data, index = null) {
      // __log(data, index, this.printKeys);
      if (Array.isArray(this.printKeys)) {
        // If printKeys is array of strings
        if (typeof this.printKeys[index || 1] === 'string') {
          return data[this.printKeys[index || 1]];
        }
        // If printKeys is array of objects
        const key = this.printKeys[index || 0];
        return data[key?.subText];
      }
      // If printKeys is single object
      return data[this.printKeys.subText];
    },
    async sendRequest() {
      this.loading = true;
      try {
        const response = await axios.post(this.endpoint, this.payload);
        this.values = response.data;
      } catch (error) {
        console.error('Error calculating price:', error);
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.sendRequest()
  },
  watch: {
    payload: {
      handler() {
        this.sendRequest();
      },
      deep: true
    }
  }
}
</script>

<style>

</style>
