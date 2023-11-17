<template>

    <v-form v-model="valid" @submit.prevent="submit" :id="id" >
        <v-container>
            <v-row>
                <v-col
                    v-for="(input, i) in $store.state.form.inputs"
                    :key ="i"
                    :index="input"
                    :cols='input.cols'
                    :md='input.md'
                    :sm='input.sm'
                >
                    
                    <ue-input-text
                        v-if="input.type=='text'"
                        v-model="editedItem[input.name]"    
                        :attributes="input"
                    />               
                    <v-text-field
                        v-if="input.type=='text1'"
                        :value="value[input.name]"
                        @input="update(input.name, $event)"
                        
                        :label="input.title"
                        v-bind="extraProps(input)"
                        :error-messages="errors[input.name]"
                    >
                    </v-text-field>

                    <ue-input-switch
                        v-else-if="['switch','boolean'].indexOf(input.type) +1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                    />
                    <v-switch
                        v-else-if="['switch1','boolean1'].indexOf(input.type) +1"
                        :value="value[input.name]"
                        :label="input.title"
                        :false-value="checkProp(input, 'falseValue', false)"
                        :true-value="checkProp(input, 'trueValue', true)"
                        
                        :append-icon="checkProp(input, 'appendIcon')"
                        :append-outer-icon="checkProp(input, 'appendOuterIcon')"
                        :background-color="checkProp(input, 'backgroundColor')"
                        :clear-icon="checkProp(input, 'clearIcon')"
                        :color="checkProp(input, 'color')"
                        :prepend-icon="checkProp(input, 'prependIcon')"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"
    
                        :error-messages="errors[input.name]"
                    ></v-switch>
                    
                    <ue-input-checkbox
                        v-else-if="['checkbox'].indexOf(input.type) +1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                    />
                    <v-checkbox
                        v-else-if="['checkbox1'].indexOf(input.type) +1"
                        :input-value="value[input.name]"
                        :label="input.title"
                        :false-value="false"
    
                        :color="checkProp(input, 'color', 'info')"
                        :append-icon="checkProp(input, 'appendIcon')"
                        :prepend-icon="checkProp(input, 'prependIcon')"
                        :on-icon="checkProp(input, 'onIcon')"
                        :off-icon="checkProp(input, 'offIcon')"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"
    
                        :error-messages="errors[input.name]"
                    ></v-checkbox>
                    
                    <ue-input-radio
                        v-else-if="['radio', 'enum'].indexOf(input.type)+1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                    />
                    <v-radio-group 
                        v-else-if="['radio1', 'enum1'].indexOf(input.type)+1"
                        :value="value[input.name]"
                        :active-class="checkProp(input, 'activeClass', '')"
                        :append-icon="checkProp(input, 'appendIcon')"
                        :prepend-icon="checkProp(input, 'prependIcon')"
                        :background-color="checkProp(input, 'backgroundColor')"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"
    
                        :error-messages="errors[input.name]"
    
                    >
                        <v-radio
                            v-for="(option,n) in input.options"
                            :value="option.value"
                            :key="n"
                            :label="checkProp(option, 'label', option.value)"
                            :color="checkProp(option, 'color', 'info')"
                            :id="checkProp(option, 'id', `option${n}`)"
                            :on-icon="checkProp(option, 'onIcon', '$radioOn')"
                            :off-icon="checkProp(option, 'offIcon', '$radioOff')"
    
                            v-bind="extraProps(option)"
                        ></v-radio>
                    </v-radio-group>
                    
                    <ue-input-select
                        v-else-if="['select'].indexOf(input.type)+1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                        />
                    <v-select
                        v-else-if="['select1'].indexOf(input.type)+1"
                        :value="value[input.name]"
                        
                        :label="input.title"
                        :items="input.options"
    
                        :append-icon="checkProp(input, 'appendIcon')"
                        :append-outer-icon="checkProp(input, 'appendOuterIcon')"
                        :background-color="checkProp(input, 'backgroundColor')"
                        :clear-icon="checkProp(input, 'clearIcon')"
                        :color="checkProp(input, 'color')"
                        :item-color="checkProp(input, 'itemColor', 'primary')"
                        :prepend-icon="checkProp(input, 'prependIcon')"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"
    
                        :error-messages="errors[input.name]"
                    ></v-select>
                    
                    <ue-input-file
                        v-else-if="['file'].indexOf(input.type) +1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                    />
                    <v-file-input
                        v-else-if="['file1'].indexOf(input.type) +1"
                        :value="value[input.name]"
                        
                        :accept="checkProp(input, 'accept' )"
                        :label="input.title"
                        :append-icon="checkProp(input, 'appendIcon')"
                        :append-outer-icon="checkProp(input, 'appendOuterIcon')"
                        :background-color="checkProp(input, 'backgroundColor')"
                        :clear-icon="checkProp(input, 'clearIcon')"
                        :menu-props="checkProp(input,'menuProps', {})"
                        :prepend-icon="checkProp(input, 'prependIcon')"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"
    
                        :error-messages="errors[input.name]"

                    ></v-file-input>
                    
                    <ue-input-range
                        v-else-if="['range-slider', 'progress', 'process'].indexOf(input.type)+1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                        />
                    <v-range-slider
                        v-else-if="['range-slider1', 'progress1', 'process1'].indexOf(input.type)+1"
                        :label="input.title"
                        :value="value[input.name]"
    
                        :hint="checkProp(input, 'hint')"
                        :max="checkProp(input, 'max', 100)"
                        :min="checkProp(input, 'min', 0)"
    
                        :append-icon="checkProp(input, 'appendIcon')"
                        :background-color="checkProp(input, 'backgroundColor')"
                        :prepend-icon="checkProp(input, 'prependIcon')"
                        :tick-size="checkProp(input, 'tickSize', 1)"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"    
                        
                        :error-messages="errors[input.name]"
                    ></v-range-slider>
                    
                    <ue-date
                        v-else-if="['date'].indexOf(input.type) +1"
                        v-model="editedItem[input.name]"
                        :attributes="input"
                    />
                    <v-menu 
                        v-else-if="['date1'].indexOf(input.type) +1"
                        v-model="pickDate"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        max-width="290px"
                        min-width="290px"
    
                        >
                        <template v-slot:activator="{ on }">
                            <v-text-field
                                    v-on="on"
                                    :label="input.title"
                                    prepend-inner-icon="mdi-calendar"
    
                                    :value="value[input.name]"
                                    @input="update(input.name, $event)"
                                    v-bind="extraProps(input)"
    
                                    :error-messages="errors[input.name]"
                            ></v-text-field>
                        </template>
                    
                        <v-date-picker 
                            :locale="$i18n.locale"
                            @input="pickDate = false"
                            :value="value[input.name]"
                            :label="input.title"
    
                            :active-picker="checkProp(input, 'activePicker')"
                            :color="checkProp(input, 'color')"
                            :elevation="checkProp(input, 'elevation', 15)"
    
                            @change="update(input.name, $event)"
                            v-bind="extraProps(input)"     
                        ></v-date-picker>
    
                    </v-menu>  

                    <v-menu 
                        v-else-if="['colo-picker'].indexOf(input.type) +1"
                        v-model="pickColor"
                        :close-on-content-click="false"
                        :nudge-right="40"
                        transition="scale-transition"
                        offset-y
                        max-width="290px"
                        min-width="290px"
    
                        >
                        <template v-slot:activator="{ on }">
                            <v-text-field
                                    v-on="on"
                                    :label="input.title"
                                    prepend-inner-icon="mdi-calendar"
                                    :value="value[input.name]"
                                    @input="update(input.name, $event)"
                                    v-bind="extraProps(input)"
    
                                    :error-messages="errors[input.name]"
    
                            ></v-text-field>
                        </template>
    
                        <v-color-picker
    
                            :value="value[input.name]"
                            :label="input.title"
                            :dot-size="checkProp(input, 'dotSize', 'rgba')"
                            :swatches-max-height="checkProp(input, 'maxHeight', 200)"
    
                            @input="update(input.name, $event)"
                            v-bind="extraProps(input)"
                        ></v-color-picker>
                    </v-menu>  
    
                    <v-text-field 
                        v-else-if="['color-picker'].indexOf(input.type) +1"
                        :value="value[input.name]"
                        :label="input.title"
                        v-mask="mask"
                        hide-details 
                        class="ma-0 pa-0" 
                        @input="update(input.name, $event)"
                    >
    
                        <template v-slot:append>
                            <v-menu 
                                v-model="pickColor" 
                                top 
                                nudge-bottom="105" 
                                nudge-left="16" 
                                :close-on-content-click="false"
                            >
    
                                <template v-slot:activator="{ on }">
                                    <div :style="swatchStyle(value[input.name])" v-on="on" />
                                </template>
                                <v-card>
                                    <v-card-text class="pa-0">
                                        <v-color-picker 
                                            flat 
                                            :value="value[input.name]"
                                            :label="input.title"
                                            :dot-size="checkProp(input, 'dotSize', 'rgba')"
                                            :swatches-max-height="checkProp(input, 'maxHeight', 200)"
                                            @input="update(input.name, $event)"
                                            v-bind="extraProps(input)"
                                        
                                        />
                                    </v-card-text>
                                </v-card>
                            </v-menu>
                        </template>
                    </v-text-field>
    
                    <v-otp-input
                        v-else-if="['otp'].indexOf(input.type)+1"
                        :value="value[input.name]"
                        :length="checkProp(input,'length', 6)"
    
                        @change="update(input.name, $event)"
                        v-bind="extraProps(input)"                    
                    ></v-otp-input>
                    
    
                </v-col>
    
                <v-text-field
                    v-if="loading"
                    color="success"
                    loading
                    disabled
                ></v-text-field>
            </v-row>
        </v-container>
        
        <v-divider></v-divider>

        <v-container v-if="hasSubmit">
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
                        absolute
                        right
                        >
                        {{ $tc('submit') }}
                    </ue-btn>
                </slot>
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
        // value: {
        //     type: Object,
        //     default: {}
        // },
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
        }
    },
    data() {
        return {
            id: Math.ceil(Math.random()*1000000) + "-form",
            valid: false,
            pickDate: false,
            pickColor: false,
            mask: '!XNNNNNNNN',

        }
    },

    created() {

        // Object.fromEntries(this.inputs[1].extras.map(v => ([v,true])))
        
        // console.log(this.inputs[2])
    },

    computed: {
        editedItem: {
            get () {
                return this.$store.state.form.editedItem;
            },
            set (value) {
                __log('form->editedItem set', value)
                // this.$store.commit(FORM.SET_EDITED_ITEM, value);

            }
        },
        ...mapState({
            loading: state => state.form.loading,
            errors: state => state.form.errors,

        }),
        ...mapGetters([
            'defaultItem'
        ])
    },
    
    methods: {
        update(key, value) {
            __log('form->update', key, value);
            // this.$emit('input', { ...this.value, [key]: value })
        },
        checkProp(object, prop, def = undefined) {
            return object[prop] !== 'undefined' ? object[prop] : def;
        },
        extraProps(props) {
            return Array.isArray(props.extras) ? Object.fromEntries( props.extras.map(v => ([v,true]))) : {}
        },
        swatchStyle(color){
            const { pickColor } = this
            return {
                backgroundColor: color,
                cursor: 'pointer',
                height: '30px',
                width: '30px',
                borderRadius: pickColor ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out'
            }
        },

        saveForm(callback=null, errorCallback=null){
            let fields = {};
            Object.keys(this.defaultItem).forEach( (key,i) => {
                fields[key] = (this.$store.state.form.editedItem[key]==null || this.defaultItem[key] != '') 
                    ? this.defaultItem[key]
                    : this.$store.state.form.editedItem[key]
            });

            if(!!this.$store.state.form.editedItem.id)
                fields.id = this.$store.state.form.editedItem.id;

            this.$store.commit(FORM.SET_EDITED_ITEM, fields);

            this.$store.dispatch(ACTIONS.SAVE_FORM, {item:null, callback:callback, errorCallback:errorCallback})
        },

        submit () {
            if(this.async){
                this.saveForm();
            }

            // this.$v.$touch()
        },
    }


}
</script>

<style>

</style>