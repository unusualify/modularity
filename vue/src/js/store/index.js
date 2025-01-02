import { createStore } from 'vuex'

import mediaLibrary from './modules/media-library'
import alert from './modules/alert'
import config from './modules/config'
import user from './modules/user'
import language from './modules/language'
import browser from './modules/browser'
import ambient from './modules/ambient'

export default createStore({
  modules: {
    ambient,
    user,
    alert,
    config,
    language,
    mediaLibrary,
    browser
  }

})
// export default new Vuex.Store({
//     modules
// });
