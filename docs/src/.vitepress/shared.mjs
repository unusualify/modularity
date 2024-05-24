import { defineConfig } from "vitepress";


export const shared = defineConfig({
    title: "Modularity",

    vite: {
        server: {
            host: '0.0.0.0',
            port: '8080',
            strictPort: true,
            headers: { 'Access-Control-Allow-Origin': '*' },
            watch: {
              usePolling: true
            },
            // proxy:{
            //     '/docs': {
            //         target: 'http://crm.template:8080',
            //         changeOrigin: true,
            //         rewrite: (path) => path.replace(/^\/docs/, '')
            //     }
            // }
            // proxy: 'http://nginx'
        }
      },

})
