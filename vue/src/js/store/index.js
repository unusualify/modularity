import { createStore } from 'vuex'

import mediaLibrary from './modules/media-library'
import alert from './modules/alert'
import config from './modules/config'
import currentUser from './modules/currentUser'

export default createStore({
  modules: {
    alert,
    config,
    currentUser,
    mediaLibrary
  }

})
// export default new Vuex.Store({
//     modules
// });
