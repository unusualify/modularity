import { mapState, mapGetters } from 'vuex'
import htmlClasses from '@/utils/htmlClasses'

export default {
    props: {
        value: {
            type: Boolean,
            default: false
        },
        cancelText: {
            type: String,
            default: ''
        },
        confirmText: {
            type: String,
            default: ''
        },
    },
    data() {
        return {
            id: Math.ceil(Math.random()*1000000) + "-modal",
            show: this.value
        }
    },

    computed: {
        // show: {
        //     get () {
        //         return this.value
        //     },
        //     set (value) {
        //         // __log('mixins/modal->show->setter', value)
        //         this.$emit('input', value)
        //         // __log(this.show)
        //     }
        // },
        textCancel() {
            return this.cancelText != '' ? this.cancelText : this.$t('cancel') 
        },
        textConfirm() {
            return this.confirmText != '' ? this.confirmText : this.$t('confirm') 
        },
    },
    methods: {
        setValue(e) {
            __log('setValue', e.target.name)
            this[e.target.name] = e.target.value;
        },
        openModal(){
            this.show = true;
        },
        closeModal(){
            this.show = false;
        },

        cancelModal(){
            if(typeof this.cancelCallback == "undefined"){
                this.closeModal();
            }else{
                this.cancelCallback();
            }
            this.$emit('cancel')
        },

        confirmModal(){
            if(typeof this.confirmCallback == "undefined"){
                this.closeModal();
            }else{
                this.confirmCallback();
            }
            this.$emit('confirm')
        },
    }
}
