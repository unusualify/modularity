# Environment

Vuetify Version: 3.3.1
Last Working Version: 3.3.0
Vue Version: 3.3.4
OS: Docker Container
Node Version: 18.14.2
Package Manager: npm@9.5.0
Compiler: vue-cli
@vue/cli Version: 5.0.8
webpack Version: 5.84.1

# Steps to reproduce
1. using vuetifyPlugin in vue.config for changing sass-variables
```
    new VuetifyPlugin({
        styles: {
            configFile: 'src/sass/themes/' + APP_THEME + '/_settings.scss'
        }
    }),
```
2. importing ./src/js/plugins/vuetify.js in a config file
```
    import { createVuetify } from 'vuetify'
    ...

    import 'styles/themes/_main.scss' // 'vuetify/styles' being imported inside this file

    ...

    import * as components from 'vuetify/lib/components'
    import * as directives from 'vuetify/lib/directives'
```
3. 
```
    *** 'styles/themes/_main.scss' file ***
    @import
        url('https://fonts.googleapis.com/css?family=Montserrat:200,400,600,800,900'),
        'abstract/variables',
        'vuetify/styles',
        ...
    *** ****
```

# Expected Behavior
It should have been compiled successfully, but the compiler occurred this error especially in 3.3.1. This isn't about npm cache or any npm issue. Mostly, I retried solution advices, but when I have downgraded to 3.3.0, it doesn't give any error. It works, just as it should be.

While I was checking out your last commits, I have realized you to add exports entry for moduleResolution=bundler. commit "89ac54c". That might be the problem.
# Actual Behavior

ERROR in ./src/js/plugins/vuetify.js 8:0-40
Module not found: Error: Default condition should be last one
Did you mean './vuetify'?
Requests that should resolve in the current directory need to start with './'.
Requests that start with a name are treated as module requests and resolve within module directories (node_modules, {_root}/{_custom_path}/vue/node_modules, {_root}/{_custom_path}/vue/node_modules/@vue/cli-service/node_modules, {_root}/node_modules).
If changing the source code is not an option there is also a resolve options called 'preferRelative' which tries to resolve these kind of requests in the current directory too.
