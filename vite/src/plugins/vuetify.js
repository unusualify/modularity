
import { createVuetify } from 'vuetify'

import 'vuetify/styles'
// import '@fortawesome/fontawesome-free/css/all.min.css' // Ensure you are using css-loader
// import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader

function loadIcons($font){
    const locales = require.context('../config/icons', true, /[A-Za-z0-9-_,\s]+.json$/i);
    const messages = {};

    locales.keys().forEach(key => {
        const matched = key.match(/([A-Za-z0-9-_]+)\./i);
        if(matched && matched.length > 1){
            const locale = matched[1];
            messages[locale] = locales(key);
        }
    })

    return messages;
}

const opts = {
    theme: {
        themes: {
            light: {
                primary: '#3f51b5',
                secondary: '#696969',
                accent: '#8c9eff',
                error: '#b71c1c',
            },
            dark: {
                background: '#ddd'
            }
        },
    },
    icons: {
        // component: VIcon,
        iconfont: 'mdi' || 'fa', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4' || 'faSvg'
        values: {

            check: 'mdi-check',

            creditCards : 'mdi-credit-card-search',

            delete: 'mdi-delete',

            edit: 'mdi-pencil',

            filter_list: 'mdi-filter',

            info: 'mdi-information',

            list: 'mdi-dots-vertical-circle-outline',

            media: 'mdi-image-album',
            modules: 'mdi-view-module',

            package: 'mdi-package',
            permission: 'mdi-account-arrow-right',
            product: 'mdi-dropbox',

            role: 'mdi-account-key',

            support: 'mdi-lifebuoy',

            // userAdd: 'fa-user-plus',
            userAdd: 'mdi-account-edit',
            users: 'mdi-account-multiple',
        },
    },
}

export default createVuetify(opts);
