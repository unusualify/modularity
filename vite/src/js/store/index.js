import Vue from 'vue';
import Vuex from 'vuex';

import mediaLibrary from './modules/media-library'
import alert from './modules/alert'
import config from './modules/config'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        alert,
        config,
        mediaLibrary
    }
});
// export default new Vuex.Store({
//     modules
// });