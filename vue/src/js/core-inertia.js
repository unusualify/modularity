import { createApp, h } from 'vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
// import * as ZiggyModule from '#/vendor/tightenco/ziggy'
import { ZiggyVue } from '#/vendor/tightenco/ziggy'


// Plugins
import UEConfig from '@/plugins/UEConfig'
import { setupInertiaInterceptors } from '@/setup/inertia-interceptors'

// check if Ziggy #vendor/tightenco/ziggy exists
// const hasZiggy = import.meta.glob('#/vendor/tightenco/ziggy', { eager: true })
// let Ziggy = null

// if(hasZiggy){
//   Ziggy = await import('#/vendor/tightenco/ziggy')
// }
// const Ziggy = __HAS_ZIGGY__ ? ZiggyModule : null

// Setup Inertia request interceptors
setupInertiaInterceptors()

createInertiaApp({
    title: (title) => {
      return `${title}`
    },
    resolve: (name) => {
      const segments = name.split('/')
      let layoutName = segments.pop()
      let moduleRouteName = segments.length > 0 ? segments.pop() : null
      let moduleName = null

      if(segments.length > 0){
        moduleName = segments.pop()
      }

      const customPages = import.meta.glob('./Pages/customs/**/*.vue', { eager: true })

      let page = customPages[`./Pages/customs/${name}.vue`]

      if(!page){
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })

        page = pages[`./Pages/${layoutName}.vue`]

        if(!page){
          console.warn(`Page component not found for: ${name}`)
          console.warn('Layout name:', layoutName)
          console.warn('Available pages:', Object.keys(pages).sort())
          return Promise.resolve({
            default: () => h('div', {
              style: 'padding: 20px; text-align: center; color: #666; font-family: system-ui;'
            }, [
              h('h2', { style: 'color: #dc3545; margin-bottom: 20px;' }, `Page not found: ${name}`),
              h('h3', { style: 'margin-bottom: 10px;' }, 'Available components:'),
              h('ul', { style: 'text-align: left; display: inline-block; max-height: 300px; overflow-y: auto;' },
                Object.keys(pages).sort().map(path =>
                  h('li', {
                    style: 'margin: 2px 0; font-family: monospace; font-size: 12px;'
                  }, path.replace('./Pages/', '').replace('.vue', ''))
                )
              )
            ])
          })
        }
      } else {
        // console.info('Module page found:', name, page)
      }

      return page
    },
    setup({ el, App, props, plugin }) {
      const app = createApp({ render: () => h(App, props) })
          .use(plugin)

      // if(__HAS_ZIGGY__){
      if(true){
        console.debug('[modularity]: found on vendor/tightenco/ziggy')
        app.use(Ziggy.ZiggyVue)
      } else {
        console.debug('[modularity]: no found on vendor/tightenco/ziggy')
      }
      app.use(UEConfig)

      // // Global properties
      app.component('ue-link', Link)

      return app.mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
