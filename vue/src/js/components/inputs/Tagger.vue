<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="v-input-tagger"
    >
    <template v-slot:default="defaultSlot">
      <v-combobox
        v-bind="$lodash.pick(boundProps, ['variant', 'menuProps', 'selectClasses', 'selectLabel', 'dense', 'density', 'color'])"

        :label="label"
        hide-selected

        :items="items"
        :loading="loading"
        :multiple="multiple"

        :itemValue="itemValue"
        :itemTitle="itemTitle"

        v-model="model"
        v-model:search="search"
        :custom-filter="filter"
      >
        <template v-slot:selection="{ item, index }">
          <v-chip v-if="item === Object(item)"
            :color="`${item.raw.color}-lighten-3`"
            :text="item.title"
            size="small"
            variant="flat"
            closable
            label
            @click:close="removeSelection(index)"
          ></v-chip>
        </template>
        <template v-slot:item="{ props, item }">
          <v-list-item v-if="item.raw.header && search">
            <span class="mr-3">Create</span>
            <v-chip
              :color="`${colors[nonce - 1]}-lighten-3`"
              size="small"
              variant="flat"
              label
            >
              {{ search }}
            </v-chip>
          </v-list-item>
          <v-list-subheader v-else-if="item.raw.header" :title="props.title"></v-list-subheader>
          <v-list-item v-else @click="props.onClick">
            <v-text-field
              v-if="editingItem === item.raw"
              v-model="editingItem[itemTitle]"
              bg-color="transparent"
              class="mr-3"
              density="compact"
              variant="plain"
              autofocus
              hide-details
              @click.stop
              @keydown.stop
              @keyup.enter="edit(item.raw)"
            ></v-text-field>
            <v-chip
              v-else
              :color="`${item.raw.color}-lighten-3`"
              :text="props.title"
              variant="flat"
              label
            ></v-chip>
            <template v-slot:append>
              <v-btn
                :color="editingItem !== item.raw ? 'primary' : 'success'"
                :icon="editingItem !== item.raw ? 'mdi-pencil' : 'mdi-check'"
                size="small"
                variant="text"
                @click.stop.prevent="edit(item.raw)"
              ></v-btn>
            </template>
          </v-list-item>
        </template>
      </v-combobox>
    </template>
  </v-input>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

export default {
  name: 'v-input-tagger',
  emits: [...makeInputEmits],
  components: {

  },
  props: {
    ...makeInputProps(),
    items: {
      type: Array,
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    itemValue: {
      type: String,
      default: 'id',
    },
    itemTitle: {
      type: String,
      default: 'name',
    },
    fetchEndpoint: {
      type: String,
      required: true,
    },
    updateEndpoint: {
      type: String,
      required: true,
    },
    colors: {
      type: Array,
      default: () => ['green', 'purple', 'indigo', 'cyan', 'teal', 'orange'],
    },
    returnItemValue: {
      type: Boolean,
      default: false,
    },
  },
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  data: function () {
    return {
      editingItem: null,
      nonce: 1,
      search: null,
      // items: [
      //   { header: true, title: 'Select an option or create one' },
      //   {
      //     title: 'Foo',
      //     color: 'blue',
      //   },
      //   {
      //     title: 'Bar',
      //     color: 'red',
      //   },
      // ],
      model: [],
    }
  },
  computed: {
    handleKey () {
      if (this.returnItemValue) {
        return this.itemValue
      }

      return this.itemTitle
    },
  },
  watch: {
    model (val, prev) {
      if (val.length === prev.length) return

      this.model = val.map(v => {
        if (typeof v === 'string') {
          v = {
            [this.itemTitle]: v,
            [this.itemValue]: v,
            color: this.colors[this.nonce % this.colors.length],
          }

          this.items.push(v)

          this.nonce++
        }

        return v
      })

      let newInput = this.model.map(v => v[this.handleKey])

      if (newInput !== this.input) {
        this.input = newInput
      }
    },
  },
  methods: {
    edit (item) {
      if (!this.editingItem) {
        this.editingItem = item
      } else {
        this.editingItem = null
      }
    },
    filter (value, queryText, item) {
      const toLowerCaseString = val =>
        String(val != null ? val : '').toLowerCase()

      const query = toLowerCaseString(queryText)

      const availableOptions = this.items.filter(x => !this.model.includes(x))
      const hasAnyMatch = availableOptions.some(
        x => !x.header && toLowerCaseString(x[this.itemTitle]).includes(query)
      )
      if (item.raw.header) return !hasAnyMatch

      const text = toLowerCaseString(item.raw[this.itemTitle])

      return text.includes(query)
    },
    removeSelection (index) {
      this.model = [...this.model.slice(0, index), ...this.model.slice(index + 1)]
    },
  },

  mounted () {
    this.model = this.items.reduce((acc, item) => {
      if (item.header) return acc

      if (this.input.includes(item[this.handleKey])) {
        acc.push(item)
      }

      return acc
    }, [])

  },

  created() {

  }
}
</script>

<style lang="sass">
  .v-input-tagger


</style>

<style lang="scss">

</style>
