import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import dns from 'dns'

dns.setDefaultResultOrder('verbatim')

// https://vitejs.dev/config/
export default defineConfig({
    server: {
        open: '/',
    },
    plugins: [
        vue(),
        vuetify({ autoImport: true }), // Enabled by default

    ],
})
