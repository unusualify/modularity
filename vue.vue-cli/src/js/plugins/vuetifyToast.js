import Vue from 'vue'
import Vuetify, { VIcon } from 'vuetify'
import VuetifyToast from 'vuetify-toast-snackbar'

const vuetifyToastSettings = {
  x: 'right', // default
  y: 'top', // default
  color: 'info', // default
  // icon: 'info',
  // iconColor: '', // default
  // classes: [
  // 	'body-2'
  // ],
  timeout: 10000 // default
  // dismissable: true, // default
  // multiLine: false, // default
  // vertical: false, // default
  // queueable: false, // default
  // showClose: false, // default
  // closeText: '', // default
  // closeIcon: 'close', // default
  // closeColor: '', // default
  // slot: [], //default
  // shorts: {
  // 	custom: {
  // 		color: 'purple'
  // 	}
  // },
  // property: '$toast' // default
}

Vue.use(Vuetify)

const toastDefault = {
  // $vuetify: Vuetify.framework
}
// console.log( { ...toastDefault, ...vuetifyToastSettings } )
Vue.use(VuetifyToast, { ...toastDefault, ...vuetifyToastSettings })

// export default vuetifyToastSettings;
