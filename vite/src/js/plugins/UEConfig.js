import _ from 'lodash';

// plugin components
// import VFormBase from 'vuetify-form-base';
import VCustomFormBase from '__components/others/CustomFormBase.vue';

// Template Components
import UEModal from '__components/modals/Modal.vue';
import UEModalTest from '__components/modals/ModalTest.vue';

import UEModalMedia from '__components/modals/ModalMedia.vue';

// global mixins
import { PropsMixin } from '@/mixins';

// mutations
import { MEDIA_LIBRARY } from '@/store/mutations';

const componentIncludes = {
    '__components': 'ue',
    '__components/layouts': 'ue',
    '__components/inputs': 'ue-input',
    '__components/customInputs': 'ue-custom-input'
}


// const includeGlobalComponents = require.context('__components', false, /[A-Za-z]*(?<!_)\.vue$/i);
const globalComponents = import.meta.globEager('__components/base/*(?<!_).vue');

// const includeLayouts = require.context('__components/layouts/', false, /[A-Za-z]*(?<!_)\.vue$/i);
// const includeFormInputs = require.context('__components/inputs', true, /\.vue$/i);
// const includeCustomFormInputs = require.context('__components/customInputs', true, /\.vue$/i);

// Add-ons
import vuetify from '@/plugins/vuetify';

// Store
import store from '@/store'

// Config
import i18n from '@/config/i18n'

// Directives
import { VueMaskDirective } from 'v-mask';

// Directives
import SvgSprite from '@/directives/svg'


export default UEConfig = (app) => {
    return {
        install (app, opts) {
            // all components
            // app.component('v-form-base', VFormBase);
            app.component('v-custom-form-base', VCustomFormBase);

            // Vue.component('ue-main', UEMain);
            // Vue.component('ue-sidebar', UESidebar);
            // Vue.component('ue-list-group', UEListGroup);
            // Vue.component('ue-list-element', UEListElement);
            // Vue.component('ue-footer', UEFooter);
            // Vue.component('ue-form', UEForm);
            // Vue.component('ue-form-base', UEFormBase);
            // Vue.component('ue-logout-dialog', UELogoutDialog);
            // Vue.component('ue-alert', UEAlert);
            // Vue.component('ue-btn', UEButton);
            app.component('ue-modal', UEModal)
            app.component('ue-modal-test', UEModalTest)
            app.component('ue-media', UEModalMedia)

            // Vue.component('ue-medialibrary', UEMediaLibrary)

            // includeGlobalComponents.keys().forEach((path) => {
            //     const prefix = "ue";
            //     const fileName = path.split('/').pop().split('.')[0];
            //     const componentName = prefix + fileName.replace(/[A-Z]/g, m => "-" + m.toLowerCase());
            //     // __log(componentName)
            //     Vue.component(componentName, require('__components/' + fileName + '.vue').default);
            // });

            Object.entries(globalComponents).forEach(([path, m]) => {
                console.log(path, m)
            })

            // includeLayouts.keys().forEach((path) => {
            //     const prefix = "ue";
            //     const fileName = path.split('/').pop().split('.')[0];
            //     const componentName = prefix + fileName.replace(/[A-Z]/g, m => "-" + m.toLowerCase());
            //     // __log(componentName)
            //     Vue.component(componentName, require('__components/layouts/' + fileName + '.vue').default);
            // });

            // includeFormInputs.keys().forEach((path) => {
            //     const prefix = "ue-input";
            //     const fileName = path.split('/').pop().split('.')[0];
            //     const componentName = prefix + fileName.replace(/[A-Z]/g, m => "-" + m.toLowerCase());
            //     // __log(componentName)
            //     Vue.component(componentName, require('__components/inputs/' + fileName + '.vue').default);
            // });
            // includeCustomFormInputs.keys().forEach((path) => {
            //     const prefix = "v-custom-input";
            //     const fileName = path.split('/').pop().split('.')[0];
            //     const componentName = prefix + fileName.replace(/[A-Z]/g, m => "-" + m.toLowerCase());
            //     // __log(componentName)
            //     Vue.component(componentName, require('__components/customInputs/' + fileName + '.vue').default);
            // });

            app.use(vuetify);
            app.use(store);

            app.mixin(PropsMixin);

            // Global Vue mixin : Use global mixins sparsely and carefully!
            app.mixin({
                // vuetify,
                // i18n,
                methods: {
                    openFreeMediaLibrary: function () {
                        // __log('openFreeMedialibrary triggered')
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_CONNECTOR, null) // reset connector
                        this.$store.commit(MEDIA_LIBRARY.RESET_MEDIA_TYPE) // reset to first available type
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_REPLACE_INDEX, -1) // we are not replacing an image here
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MAX, 0) // set max to 0
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_FILESIZE_MAX, 0) // set filesize max to 0
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_WIDTH_MIN, 0) // set width min to 0
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_HEIGHT_MIN, 0) // set height min to 0
                        this.$store.commit(MEDIA_LIBRARY.UPDATE_MEDIA_MODE, false) // set the strict to false (you can change the active type)

                        if (this.$root.$refs.main  && this.$root.$refs.main.$refs.mediaLibrary)
                            this.$root.$refs.main.$refs.mediaLibrary.openModal()
                        // if (this.$root.$refs.mediaLibrary) this.$root.$refs.mediaLibrary.open()
                    }
                },

            })


            // // Configurations
            // Vue.config.productionTip = isProd
            // Vue.config.devtools = true
            // app.config.globalProperties.$http = axios

            window.$trans = app.config.globalProperties.$trans = function (key, defaultValue) {
                return _.get(window[process.env.JS_APP_NAME].unusualLocalization.lang, key, defaultValue)
                // return get(window[process.env.JS_APP_NAME].unusualLocalization.lang, key, defaultValue)
            }

            // axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

            // axios.interceptors.response.use((response) => response, (error) => {
            //     globalError('CONTENT', error)

            //     return Promise.reject(error)
            // })

            // // Plugins
            // Vue.use(VueTimeago, {
            //     name: 'timeago', // component name
            //     locale: window[process.env.JS_APP_NAME].twillLocalization.locale,
            //     locales: mapValues(locales, 'date-fns')
            // })

            // Directives
            Vue.directive('mask', VueMaskDirective);
            Vue.use(SvgSprite)
            // Vue.use(Tooltip)
            // Vue.use(Sticky)

        }
    }
}

