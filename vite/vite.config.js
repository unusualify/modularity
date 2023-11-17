import { defineConfig, loadEnv, splitVendorChunkPlugin  } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import { resolve, dirname } from 'node:path'
import vue from '@vitejs/plugin-vue'
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import path from 'path';
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
// import { ViteMpPlugin } from 'vite-plugin-mp'

// https://vitejs.dev/config/
export default defineConfig(({ command, mode }) => {
    // Load env file based on `mode` in the current working directory.
    // Set the third parameter to '' to load all env regardless of the `VITE_` prefix.
    const ENV = loadEnv(mode, process.cwd(), '')
    const URL = ENV.APP_URL || 'crm.template'

    const ROOT = dirname(fileURLToPath(import.meta.url)); // /var/www/crm_template/packages/oobook/crm-base/vite
    // fileURLToPath(import.meta.url), // /var/www/crm_template/packages/oobook/crm-base/vite/vite.config.js
    // import.meta.url // file:///var/www/crm_template/packages/oobook/crm-base/vite/vite.config.js

    return {
        // vite config
        define: {
            process: {
                env: {
                    JS_APP_NAME: ENV.JS_APP_NAME
                }
            }
        },
        // root: path.join(__dirname, "src"),
        envPrefix: 'VITE_',
        appType: 'mpa',
        // optimizeDeps: { disabled: false },


        build: {
            // sourcemap: false,
            sourcemap: 'inline',

            manifest: 'unusual-manifest.json',
            outputDir: resolve(ROOT, "dist"),
            // publicDir: '../public',

            cssCodeSplit: false,
            // lib: {
            //     // Could also be a dictionary or array of multiple entry points
            //     // entry: path.resolve(__dirname, '/src/js/core-index.js'),
            //     entry: 'src/js/core-index.js',
            //     name: 'unusual-test',
            //     // the proper extensions will be added
            //     // fileName: 'core-index',
            //     fileName: (format) => `[name].js`,
            // },
            rollupOptions: {
                input: {
                    'core-index': resolve(__dirname, 'src/js/core-index.js'),
                    'core-form' : resolve(__dirname, 'src/js/core-form.js'),
                },
                // make sure to externalize deps that shouldn't be bundled
                // into your library
                external: ['vue'],
                output: {
                    entryFileNames: `[name].js`,
                    // chunkFileNames: `[name].[hash].js`,
                    assetFileNames: `[name].[ext]`,

                    preserveModules: false,
                    // Provide global variables to use in the UMD build
                    // for externalized deps
                    globals: {
                        vue: 'Vue',
                    },
                },
            },

        },
        server: {
            // strictPort: true,

            open: false,
            proxy: {
                target: "nginx", // replace with your web server container
                proxyReq: [
                    function(proxyReq) {
                        proxyReq.setHeader('HOST', URL + ':3000'); // replace with your site host
                    }
                ]
            }
        },
        plugins: [
            vue({
                // template: { transformAssetUrls }
            }),
            VueI18nPlugin({
                // runtimeOnly: false,
                include: resolve(ROOT, './src/js/config/lang/**'), // provide a path to the folder where you'll store translation data (see below)
            }),
            vuetify({ autoImport: true }), // Enabled by default

            // ViteMpPlugin(), splitVendorChunkPlugin()
        ],
        resolve: {
            mainFields: ['index', 'main', 'setup'],
            browserField: true,
            alias: {

                src: path.resolve(__dirname, 'src'),
                '#': path.resolve(__dirname, 'src'),
                '@': path.resolve(__dirname, 'src/js'),
                'styles': path.resolve(__dirname, 'src/sass'),
                '__components': path.resolve(__dirname, 'src/js/components'),
                '__layout': path.resolve(__dirname, 'src/js/layouts'),
                '__setup': path.resolve(__dirname, 'src/js/setup/'),

            }
        },
        css: {
            preprocessorOptions: {
              scss: {
                additionalData: `
                    @import "styles/setup/_settings.scss";

                `
              }
            }
        }
    }
})

