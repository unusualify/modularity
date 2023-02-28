import Vue from 'vue'
import VueNotification from "@kugatsu/vuenotification";

Vue.use(VueNotification, {
    timer: 5,
    position: "topRight",
    showCloseIcn: true
});