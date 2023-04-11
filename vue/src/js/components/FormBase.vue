<template>

    <v-form
        :ref="reference"
        v-model="valid"
        lazy-validation
        @submit.prevent="submit"
        :id="id"
        >
        <v-container>
            <v-row>
                <v-col
                    v-if="hasStickyFrame"
                    v-bind="stickyColumnAttrs"
                    class="d-flex flex-column"
                    style="position:sticky;"
                    >
                        <div class="d-flex flex-column align-items-center" style="position:sticky;top:100px;">
                            <slot
                                v-if="hasSubmit && stickyButton"
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

                                    >
                                    {{ $t('submit') }}
                                </ue-btn>
                            </slot>

                            <slot
                                name="stickyBody"
                                :attrs="{

                                }"
                                :on="{

                                }"
                                >
                                <!-- <v-card class="mt-6" height="">
                                        <div>
                                            <v-icon>mdi-camera</v-icon>
                                            <h3>Upload</h3>
                                        </div>
                                </v-card> -->
                            </slot>

                        </div>
                        <!-- <v-spacer></v-spacer> -->

                </v-col>
                <v-col
                    v-bind="formColumnAttrs"
                    >

                    <v-custom-form-base
                      id="treeview-slot"

                      :row="rowAttribute"
                      :model="model"
                      :schema="inputSchema"

                      @update="handleUpdate"
                      @input="handleInput"
                      @resize="handleResize"
                      @blur="handleBlur"

                      >
                      <template v-slot:[`slot-inject-prepend-key-treeview-slot-permissions`]="{open}" >
                        <v-icon color="blue">
                            {{open ? 'mdi-folder-open' : 'mdi-folder'}}
                        </v-icon>
                      </template>
                      <template #slot-inject-label-key-treeview-slot-permissions="{item}" >
                        <span class="caption" >{{item.name.toUpperCase()}}</span>
                      </template>
                    </v-custom-form-base>

                </v-col>
            </v-row>
        </v-container>

        <v-container v-if="hasSubmit && !stickyButton">
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
  // name: "ue-form-base",
  props: {
    value: {
      type: Object,
      default () {
        return {}
      }
    },
    schema: {
      type: Object,
      default () {
        return {}
      }
    },
    rowAttribute: {
      type: Object,
      default () {
        return {
          noGutters: false
          // justify:'center',
          // align:'center'
        }
      }
    },
    async: {
      type: Boolean,
      default: true
    },
    hasSubmit: {
      type: Boolean,
      default: false
    },
    stickyFrame: {
      type: Boolean,
      default: false
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

  beforeCreate () {

  },

  created () {
    // __log(this.$root)
    // Object.fromEntries(this.inputs[1].extras.map(v => ([v,true])))

    // console.log(this.inputs[2])
  },

  watch: {
    inputSchema (val) {
      __log('inputSchema changed', val)
    }
  },

  computed: {
    issetModel () {
      return Object.keys(this.value).length > 0
    },
    issetSchema () {
      return Object.keys(this.schema).length > 0
    },
    hasStickyFrame () {
      return this.stickyFrame || this.stickyButton
    },
    inputSchema () {
      return this.issetSchema ? this.schema : this.$store.state.form.inputs
    },
    defaultItem: {
      get () {
        return this.issetModel ? this.value : this.$store.state.form.editedItem
      },
      set (value) {

      }
    },
    model: {
      get () {
        // console.log('formBase model getter', this.defaultItem, this.value)
        return this.defaultItem
      },
      set (value) {
        __log('model setter', value)
        // __log('ForBase.vue->model set', value)

        // if(this.issetModel)
        //     this.$emit('input', value)
        // else
        //     this.$store.commit(FORM.SET_EDITED_ITEM, value)
      }
    },

    reference () {
      return 'ref-' + this.id
    },

    formColumnAttrs () {
      return this.hasStickyFrame
        ? {
            cols: '12',
            sm: '12',
            md: '12',
            lg: '8',
            xl: '6',
            'order-lg': '0',
            'order-xl': '0'
          }
        : {
            cols: '12'
          }
    },

    stickyColumnAttrs () {
      return {
        cols: '12',
        sm: '12',
        md: '12',
        lg: '4',
        xl: '6',
        'order-lg': '1',
        'order-xl': '1'
      }
    },

    ...mapState({
      loading: state => state.form.loading,
      errors: state => state.form.errors
    })

  },

  methods: {
    validate () {
      this.$refs[this.reference].validate()
    },
    resetValidation () {
      this.$refs[this.reference].resetValidation()
    },

    handleInput (v) {
      // __log(
      //     'handleInput',
      //     v.obj.key,
      //     v.obj.value,
      //     this.model
      // )
      // this.model = this.model;
    },
    handleUpdate (v) {
      __log('handleUpdate', v)
    },
    handleResize (v) {
      // __log('handleResize', v)

    },
    handleBlur (v) {
      // __log('handleBlur', v)

    },

    saveForm (callback = null, errorCallback = null) {
      const fields = {}
      Object.keys(this.defaultItem).forEach((key, i) => {
        fields[key] = (this.model[key] == null || this.defaultItem[key] != '')
          ? this.defaultItem[key]
          : this.model[key]
      })

      if (this.model.id) { fields.id = this.model.id }

      // __log(
      //     this.defaultItem,
      //     fields,
      // );
      // return;
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
