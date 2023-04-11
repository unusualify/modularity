import { createStore } from 'vuex'

import mediaLibrary from './modules/media-library'
import alert from './modules/alert'
import config from './modules/config'

export default createStore({
  modules: {
    alert,
    config,
    mediaLibrary
  }

})
// export default new Vuex.Store({
//     modules
// });
