<!-- <template>

    <v-dialog
        v-model="dialog"
        :fullscreen="toggleFullScreen"
        :persistent="togglePersistent"
        :scrollable="toggleScrollable"
        :width="modalWidth"

        v-bind="attrs({...$props, ...$attrs})"

    >

        <template v-slot:activator="{ on, attrs }">
            <slot 
                name="activator"
                :attrs="{
                    ...attrs
                }"
                :on="{
                    ...on
                }"
                >
            </slot>
        </template>
        
        <slot 
            name="body"
            :attrs="{
            }"
            :on="{
                openDialog: this.open,
                closeDialog: this.close,
                confirmDialog: this.confirm
            }"
            :closeDialog="close"
            >
        
        </slot>
    </v-dialog>
</template> -->

<script>
import htmlClasses from '@/utils/htmlClasses'
import { h } from 'vue'

export default {
    render() {
        return h(
            'v-dialog', 
            {
                modelValue: this.dialog,
                'onUpdate:modelValue': (value) => this.$emit('update:modelValue', value)
                
            },
            {
                activator: (on, attrs) => {
                    h('slot', {name: 'activator', attrs: this.$attrs })
                }
            }

        )
    },
    props: {
        value: {
            type: Boolean
        },
        name: {
            type: String,
            default: "Item"
        },
        transition: {
            type: String,
            default: "bottom"
        },
        fullScreen: {
            type: Boolean,
            default: false
        },
        persistent: {
            type: Boolean,
            default: false
        },
        scrollable: {
            type: Boolean,
            default: false
        },
        widthType: {
            type: String,
            default: "md",
        }
    },
    data() {
        return {
            // dialog: this.value,
            widths: {
                sm: "300px",
                md: "500px",
                lg: "750px",
            },
            width: this.widthType,

            modalClass: htmlClasses.modal,
            firstFocusableEl: null,
            lastFocusableEl: null,

        }
    },
    computed: {
        dialog: {
            get () {
                return this.value
            },
            set (value) {
                __log('modal->dialog->setter', value)
                this.$emit('input', value)
            }
        },
        full: {
            get () {
                return this.fullScreen
            },
            set (value) {
                this.$emit('screenListener', this.fullScreen)
            }
        },
        togglePersistent() {
            return this.persistent;
        },
        toggleFullScreen() {
            return this.full;
        },
        toggleScrollable() {
            return this.scrollable;
        },
        modalWidth() {
            return this.widths[this.width];
        },
    },

    watch: {

    },

    methods: {
        toggle() {
            this.dialog = !this.dialog
        },
        close() {
            this.dialog = false
        },
        open() {
            this.dialog = true
        },
        confirm() {
            this.dialog = false
        },

        attrs(attrs) {
            __log(attrs)
            return attrs;
        }
    },
    beforeDestroy: function () {

    },
    created() {
        // setInterval((self) => {
        //     __log(self.dialog)
        // }, 1000, this)
    }
}
</script>

<style>

</style>