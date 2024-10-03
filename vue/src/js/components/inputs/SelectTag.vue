<template>
  <div>
  <v-autocomplete
    v-bind="$bindAttributes()"
    v-model="input"
    variant="outlined"
    clearable
    closable-chips
    chips
    clear-on-select
    :label="label"
    :items="filteredElements"
    multiple
    @update:model-value="handleUpdateModelValue"
    @update:search="handleSearch"
    @change="input"
  ></v-autocomplete>
  <v-text-field
    type="hidden"
    hidden
    name="tags"
    id="tags-form-input">
  </v-text-field>
  </div>


</template>

<script>
import { computed } from 'vue';
import { useInput, makeInputProps, makeInputEmits } from '@/hooks';

export default {
  name: 'v-select-tag',
  emits: [...makeInputEmits],
  props: {
    ...makeInputProps(),
    endpoint: {
      type: String
    },
    selected: {
      type: Array
    }
  },
  setup(props, context) {
    const inputHook = useInput(props, context);
    return {
      ...inputHook,
    };
  },
  data() {
    return {
      value: this.selected,
      elements: [],
      page: 1,
      lastPage: -1,
      customElement: null,
      selectedItems: [], // To keep track of selected items
    };
  },
  methods: {
    getItemsFromApi(event) {
      return new Promise(() => {
        this.$axios.get(this.fullUrl)
          .then(response => {
            if (this.lastPage < 0)
              this.lastPage = response.data.resource.last_page;
            if (this.search == '') {
              this.elements = this.elements.concat(response.data.resource.data ?? []);
            } else {
              this.elements = response.data.resource.data ?? [];
            }
            this.page++;

            if (!!this.input) {
              let searchContinue = false;
              let self = this;
              if (this.input && this.input.length > 0 && !self.elements.find((o) => o.id == this.input)) {
                searchContinue = true;
              }

              if (searchContinue)
                this.getItemsFromApi();
            }
          });
      });
    },
    makeReference(key) {
      return `${key}-${states.id}`;
    },
    handleUpdateModelValue(newValue) {
      this.value = newValue;
      this.updateModelValue(newValue.toString());
      document.getElementById('tags-form-input').value = newValue.toString()
      this.$emit('change', newValue.toString());
    },
    handleSearch(value) {
      // If the search query is empty, only remove the custom element if it's not selected
      if(!value){
        return;
      }
      if (!value.trim()) {
        if (this.customElement !== null && !this.selectedItems.includes(this.customElement)) {
          const index = this.elements.indexOf(this.customElement);
          if (index !== -1) {
            this.elements.splice(index, 1);
          }
          this.customElement = null;
        }
        return;
      }

      // Convert the Proxy to a regular array if necessary
      const regularArray = Array.isArray(this.elements) ? this.elements : Array.from(this.elements);

      // Convert search text to lowercase for case-insensitive search
      const lowerSearchText = value.toLowerCase();

      // Check if the value exists in the array
      const valueExists = regularArray.some(item =>
        typeof item === 'string' && item.toLowerCase() === lowerSearchText
      );

      if (!valueExists) {
        if (this.customElement !== null) {
          // Update the existing custom element in the array
          const index = this.elements.indexOf(this.customElement);
          if (index !== -1) {
            this.elements.splice(index, 1, value);
          }
        } else {
          // Add the new value to the array
          this.elements.push(value);
          //Create a tag with call
          //Refetch list
        }
        // Update the tracked custom element
        this.customElement = value;
      }
    },
  },
  computed: {
    input: {
      get() {
        if (Array.isArray(this.value) && this.value.length > 0) {
          if (typeof this.value[0] === 'object') {
            return this.value.map(e => e.value);
          }
          return this.value;
        }
        return [];
      },
      set(val) {
        this.handleUpdateModelValue(val);
      }
    },
    filteredElements() {
      return this.elements.filter(element => !this.input.includes(element.id));
    },
    queryParameters() {
      let query = new URLSearchParams({
        page: this.page,
      });
      return query.toString();
    },
    fullUrl() {
      return `${this.endpoint}?${this.queryParameters}`;
    },
  },
  created() {
    this.getItemsFromApi();
  },
  watch: {
    selected: {
      handler(newSelected) {
        this.value = newSelected || [];
      },
      immediate: true,
    },
  },
};
</script>
