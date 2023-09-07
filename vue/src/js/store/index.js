import { createStore } from 'vuex'

import mediaLibrary from './modules/media-library'
import alert from './modules/alert'
import config from './modules/config'
import currentUser from './modules/currentUser'
import language from './modules/language'

export default createStore({
  modules: {
    alert,
    config,
    language,
    currentUser,
    mediaLibrary
  }

})
// export default new Vuex.Store({
//     modules
// });
