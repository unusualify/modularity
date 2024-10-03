<template>
  <div>
    <v-menu v-model="menu" :close-on-content-click="false" location="bottom">
      <template #activator="{ props }">
        <v-btn color="primary" v-bind="props">
          {{ buttonText }}
        </v-btn>
      </template>

      <v-card min-width="300">
        <v-card-text>
          <ue-form
            ref="form"
            :schema="schema"
            has-submit
            v-model="localFilterState"
            @submit="submitFilter"
          >
            <template v-slot:submit>
              <v-btn
                color="primary"
                class="mt-4 mr-2"
                type="button"
                :loading="loading"
                @click="submitFilter"
              >
                Apply Filters
              </v-btn>
              <v-btn
                color="secondary"
                class="mt-4 ml-2"
                type="button"
                :loading="loading"
                @click="handleClear"
              >
                Clear Filters
              </v-btn>

            </template>
          </ue-form>
        </v-card-text>
      </v-card>
    </v-menu>
  </div>
</template>

<script>
import { reactive, ref, watch } from 'vue';

export default {
  name: 'UEDropdownFilter',
  props: {
    buttonText: {
      type: String,
      default: 'Filter'
    },
    loading: {
      type: Boolean,
      default: false
    },
    page: {
      type: Number,
      required: true
    },
    type: {
      type: String,
      required: true
    },
    tags: {
      type: Array,
      default: () => []
    },
    filterState: {
      type: Object,
      required: true
    },
    filterModel: {
      type: Object,
      required: true
    },
    schema: {
      type: Object || Array,
      required: true
    }
  },
  emits: ['update:filterState', 'submit', 'clear'],
  setup(props, { emit }) {
    const localFilterState = ref({ ...props.filterState });
    const menu = ref(false);

    watch(() => props.filterState, (newValue) => {
      localFilterState.value = { ...newValue };
    });

    const submitFilter = () => {
      console.log(localFilterState)
      emit('update:filterState', { ...localFilterState.value });
      emit('submit');
      menu.value = false;
    };

    const handleClear = () => {
      emit('clear');
      menu.value = false;
    };

    return {
      localFilterState,
      menu,
      submitFilter,
      handleClear
    };
  }
}
</script>
