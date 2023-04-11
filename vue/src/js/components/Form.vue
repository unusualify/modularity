<template>

    <v-form v-model="valid" @submit.prevent="submit" :id="id" >

        <v-container>

            <v-row>
                <v-col
                    :cols="12"
                    :sm="12"
                    :md="12"
                    :lg="4"
                    :xl="6"

                    :order-lg="1"
                    :order-xl="1"
                    class="d-flex flex-column"
                    style="position:sticky;"

                    >
                        <div class="d-flex flex-column align-items-center" style="position:sticky;top:100px;">
                            <slot
                                v-if="stickyButton && hasSubmit"
                                name="submitButton"
                                :attrs="{

                                }"
                                :on="{

                                }"
                                >
                                <ue-btn
                                    :form="id"
                                    type="submit"
                                    width="60%"
                                    class="mb-6"
                                    >
                                    {{ $t('submit') }}
                                </ue-btn>
                            </slot>

                            <!-- <v-card class="mt-6" height="">
                                    <div>
                                        <v-icon>mdi-camera</v-icon>
                                        <h3>Upload</h3>
                                    </div>
                            </v-card> -->
                        </div>
                        <!-- <v-spacer></v-spacer> -->

                </v-col>

                <v-col
                    :cols="12"
                    :sm="12"
                    :md="12"
                    :lg="8"
                    :xl="6"

                    :order-lg="0"
                    :order-xl="0"
                    >

                    <slot
                        name="body"
                        :attrs="{
                            // inputs: this.formInputs,
                            // item: this.editedItem
                        }"
                        >
                        <v-row>
                            <v-col
                                v-for="(input, i) in formInputs"
                                :key ="i"
                                :index="i"
                                :cols='input.cols'
                                :sm='input.sm'
                                :md='input.md'
                                :lg='input.lg'
                                :xl='input.xl'
                            >
                                <component
                                    :is="`ue-input-${input.type}`"
                                    v-model="model[input.name]"
                                    :attributes="input"
                                    />

                            </v-col>
                        </v-row>
                    </slot>
                </v-col>

            </v-row>

        </v-container>

        <!-- <v-divider></v-divider> -->

        <v-container v-if="!stickyButton && hasSubmit">
                <!-- <v-spacer></v-spacer> -->
                <slot
                    name="submitButton"
                    :attrs="{

                    }"
                    :on="{

                    }"
                    >
                    <ue-btn
                        :form="id"
                        type="submit"
                        right
                        >
                        {{ $t('submit') }}
                    </ue-btn>
                </slot>
        </v-container>

        <v-container>
            <v-text-field
                v-if="loading"
                color="success"
                loading
                disabled
            />
        </v-container>

    </v-form>

</template>

<script>
import { mapGetters, mapState } from 'vuex'
import { FORM } from '@/store/mutations/index'
import ACTIONS from '@/store/actions'

export default {
  // name: "ue-form",
  props: {
    value: {
      type: Object,
      default () {
        return {}
      }
    },
    inputs: {
      type: Array
    },
    async: {
      type: Boolean,
      default: true
    },
    hasSubmit: {
      type: Boolean,
      default: false
    },
    buttonFloat: {
      type: String,
      default: 'right'
    },
    buttonPosition: {
      type: String,
      default: 'bottom'
    },
    stickyButton: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      id: Math.ceil(Math.random() * 1000000) + '-form',
      valid: false
    }
  },

  created () {
    // Object.fromEntries(this.inputs[1].extras.map(v => ([v,true])))
    // console.log(this.inputs[2])
  },

  computed: {
    issetModel () {
      return Object.keys(this.value).length > 0
    },
    model: {
      get () {
        return this.issetModel ? this.value : this.$store.state.form.editedItem
      },
      set (value) {
        // __log('Form.vue->model set', value)
        // this.$store.commit(FORM.SET_EDITED_ITEM, value);

      }
    },
    formInputs: {
      get () {
        // __log(this.inputs ?? this.$store.state.form.inputs ?? [])
        return this.inputs ?? this.$store.state.form.inputs ?? []
      },
      set (value) {
        __log('form->inputs set', value)
        // this.$store.commit(FORM.SET_EDITED_ITEM, value);
      }
    },
    ...mapState({
      loading: state => state.form.loading,
      errors: state => state.form.errors
    }),

    ...mapGetters([
      'defaultItem'
    ])
  },

  methods: {
    // update(key, value) {
    //     __log('form->update', key, value);
    //     // this.$emit('input', { ...this.value, [key]: value })
    // },

    saveForm (callback = null, errorCallback = null) {
      __log(
        this.model
      )
      return
      const fields = {}

      Object.keys(this.defaultItem).forEach((key, i) => {
        fields[key] = (this.$store.state.form.editedItem[key] == null || this.defaultItem[key] != '')
          ? this.defaultItem[key]
          : this.$store.state.form.editedItem[key]
      })

      if (this.$store.state.form.editedItem.id) { fields.id = this.$store.state.form.editedItem.id }

      this.$store.commit(FORM.SET_EDITED_ITEM, fields)

      this.$store.dispatch(ACTIONS.SAVE_FORM, { item: null, callback, errorCallback })
    },

    submit () {
      if (this.async) {
        this.saveForm()
      }

      // this.$v.$touch()
    }
  }

}
</script>

<style>

</style>
