// General includes
import '__setup/setup.js';

const UECompose = function (app) {
    window.Vue = app;
    app.config.globalProperties.$ = $
    app.config.globalProperties.axios = axios
}

export default UECompose
