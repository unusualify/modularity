<template>
    <v-snackbar
        v-model="show"
        :timeout="timeout"
        :color="type"
        absolute
        top
        right
    >
        {{ text }}

        <template v-slot:action="{ attrs }">
            <v-btn
                v-bind="attrs"
                text
                @click="show = false"
            >
                {{ $tc('close') }}
            </v-btn>
        </template>
    </v-snackbar>
</template>

<script>
    import { AlertMixin } from '@/mixins'
    export default {
        mixins: [AlertMixin],
        data: () => ({
            // show: false,
            // message: null,
            // type: 'info',
            timeout: 3000
        }),
        computed: {
            text() {
                return this.message || this.defaultMessage
            },
            defaultMessage() {
                return this.$t('messages.' + this.type)
            }
        },
        methods: {
            info(message = null, timeout = 3000){
                this.open('info', message, timeout);
            },
            success(message = null, timeout = 3000){
                this.open('success', message, timeout);
            },
            warning(message = null, timeout = 3000){
                this.open('warning', message, timeout);
            },
            error(message = null, timeout = 3000){
                this.open('error', message, timeout);
            },
        }
    }
</script>