import { defineConfig } from "vitepress";


export const shared = defineConfig({
    title: "Modularity",
    srcDir: 'pages',
    cleanUrls: true,
    lastUpdated: true,
    vite: {
        server: {
            host: '0.0.0.0',
            port: '8080',
            strictPort: true,
            headers: { 'Access-Control-Allow-Origin': '*' },
            watch: {
              usePolling: true
            },

        }
      },

})
