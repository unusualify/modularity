// Plugins
import Components from 'unplugin-vue-components/vite'
import Vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import Vue from '@vitejs/plugin-vue'
import Inspect from 'vite-plugin-inspect'
import { viteCommonjs } from '@originjs/vite-plugin-commonjs'
import Modularity from './vite-plugin-modularity'

// Test Plugins
// import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
// import { ViteMpPlugin } from 'vite-plugin-mp'
// import cleanPlugin from 'vite-plugin-clean'
// import commonjs from 'vite-plugin-commonjs'
// import rollupCommonjs from '@rollup/plugin-commonjs' // Assuming you've installed it

// Utilities
import { defineConfig, loadEnv, splitVendorChunkPlugin } from 'vite'
import { resolve, dirname } from 'node:path'
import { fileURLToPath, URL } from 'node:url'
import path from 'path'
import { globSync } from 'glob'

import dotenv from 'dotenv'
import dotenvExpand from 'dotenv-expand'
// const dotenv = require('dotenv')
// const dotenvExpand = require('dotenv-expand')

const myEnv = dotenv.config({ path: path.resolve(__dirname, '../../../.env') }).parsed // prevent writing to `process.env`
dotenvExpand.expand(myEnv)

const svgConfig = (suffix = null) => {
  suffix = suffix !== null ? `-${suffix}` : ''

  return {
    output: {
      filename: `${partialsDirectory}/icons/icons${suffix}-svg.blade.php`,
      chunk: {
        name: `icons${suffix}`
      }
    },
    sprite: {
      prefix: 'icon--'
    },
    styles: {
      filename: `~svg-sprite-icons${suffix}.scss`,
      variables: {
        sprites: `icons${suffix}-sprites`,
        sizes: `icons${suffix}-sizes`,
        variables: `icons${suffix}-variables`,
        mixin: `icons${suffix}-sprites-mixin`
      }
    }
  }
}

export default defineConfig(({ command, mode }) => {
  const ENV = loadEnv(mode, process.cwd(), '')
  const envPrefix = 'VUE'

  const vendorPath = ENV.MODULARITY_VENDOR_PATH || ENV.UNUSUAL_VENDOR_PATH || 'vendor/unusualify/modularity'
  const CD_PARENT_STRING = Array(vendorPath.split('/').length + 1).fill('..').join('/') // 2 or 3 => '../../../

  const APP_THEME = ENV.VUE_APP_THEME || 'unusual'
  const VUE_DEV_PROXY = ENV.VUE_DEV_PROXY || 'http://nginx'

  const srcDir = fileURLToPath(new URL('src', import.meta.url))
  const inputDir = fileURLToPath(new URL(`${srcDir}/js`, import.meta.url))
  const inputPattern = fileURLToPath(new URL(`${inputDir}/core-*.js`, import.meta.url))

  const base = 'vendor/modularity'

  // const outDir = 'dist/modularity'
  const outDir = fileURLToPath(new URL(`${CD_PARENT_STRING}/public/${base}`, import.meta.url))
  const targetDir = fileURLToPath(new URL(`${CD_PARENT_STRING}/public`, import.meta.url))

  const assetsDir = 'assets'
  const publicDir = 'public'

  const partialsDirectory = path.resolve(__dirname, './views/partials')
  const svgIconsDirectory = path.resolve(__dirname, `${CD_PARENT_STRING}/resources/views/vendor/modularity/partials/icons`)

  return {
    // envDir: path.resolve(__dirname, '../../../.env'),
    envPrefix,
    base,
    publicDir,
    plugins: [
      Modularity({
        publicDirectory: targetDir,
        refresh: true,
        theme: APP_THEME
      }),
      Inspect(),
      Vue({
        template: { transformAssetUrls }
      }),
      Vuetify({
        styles: {
          configFile: 'src/sass/themes/' + APP_THEME + '/vuetify/_settings.scss'
        }
      }),
      Components(),
      splitVendorChunkPlugin(),
      viteCommonjs()
    ],
    resolve: {
      extensions: [
        '.js',
        '.json',
        '.jsx',
        '.mjs',
        '.ts',
        '.tsx',
        '.vue'
      ],
      alias: {
        vue: 'vue/dist/vue.esm-bundler.js',

        'vue-template-compiler$': '~/vue-template-compiler/build.js',
        'prosemirror-tables': `${path.join(__dirname, 'node_modules/prosemirror-tables/src/index.js')}`,
        'prosemirror-state': `${path.join(__dirname, 'node_modules/prosemirror-state/src/index.js')}`,
        'prosemirror-view': `${path.join(__dirname, 'node_modules/prosemirror-view/src/index.js')}`,
        'prosemirror-transform': `${path.join(__dirname, 'node_modules/prosemirror-transform/src/index.js')}`,

        '@': fileURLToPath(new URL(`${srcDir}/js`, import.meta.url)),
        styles: fileURLToPath(new URL(`${srcDir}/sass`, import.meta.url)),
        css: fileURLToPath(new URL(`${srcDir}/css`, import.meta.url)),
        __components: fileURLToPath(new URL(`${srcDir}/js/components`, import.meta.url)),
        __hooks: fileURLToPath(new URL(`${srcDir}/js/hooks`, import.meta.url)),
        __layouts: fileURLToPath(new URL(`${srcDir}/js/layouts`, import.meta.url)),
        __setup: fileURLToPath(new URL(`${srcDir}/js/setup`, import.meta.url)),
        '~': fileURLToPath(new URL('./node_modules', import.meta.url))
      }
    },
    build: {
      // target: 'esnext',
      // target: "ES2022", // <--------- ✅✅✅✅✅✅
      target: 'es2015',

      // commonjsOptions: { transformMixedEsModules: true }, // Change

      manifest: 'modularity-manifest.json',
      cssCodeSplit: true,
      outDir,
      emptyOutDir: true,
      assetsDir,
      assetsInlineLimit: 0,
      sourcemap: true,
      rollupOptions: {
        // input: {
        //   'core-auth': resolve(__dirname, 'src/js/core-auth.js'),
        //   'core-dashboard': resolve(__dirname, 'src/js/core-dashboard.js'),
        //   'core-form': resolve(__dirname, 'src/js/core-form.js'),
        //   'core-free': resolve(__dirname, 'src/js/core-free.js'),
        //   'core-index': resolve(__dirname, 'src/js/core-index.js')
        // },
        input: Object.fromEntries(
          globSync(inputPattern).map(file => [
            // This remove `src/` as well as the file extension from each
            // file, so e.g. src/nested/foo.js becomes nested/foo
            path.relative(
              inputDir,
              file.slice(0, file.length - path.extname(file).length)
            ),
            // This expands the relative paths to absolute paths, so e.g.
            // src/nested/foo becomes /project/src/nested/foo.js
            fileURLToPath(new URL(file, import.meta.url))
          ])
        ),
        output: {
          manualChunks: function manualChunks (id) {
            if (id.match(/node_modules\/(vuetify|fine-uploader\/|awesome-phonenumber)/)) {
              return id.toString().split('node_modules/')[1].split('/')[0].toString()
            }
          },
          assetFileNames: (assetInfo) => {
            let extType = assetInfo.name.split('.').at(1)
            if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
              extType = 'img'
            }

            if (/eot|woff2?|ttf/i.test(extType)) {
              extType = 'fonts'
            }

            // return `modularity/${extType}/[name].asset[extname]`;
            return `${extType}/[name].[hash][extname]`
          },

          // dir: 'js',
          makeAbsoluteExternalsRelative: true,

          chunkFileNames: 'js/[name].[hash].js',
          entryFileNames: 'entries/[name].[hash].js'

          // preserveModules: true
          // globals: {
          //     jquery: 'window.$',
          //     axios: 'window.axios',
          //     lodash: 'window._',
          // },
        }
      }
    },
    css: {
      preprocessorOptions: {
        scss: {
          additionalData: `
            @use "styles/themes/${APP_THEME}/_additional.scss" as *;
          `,

          sassOptions: {
            outputStyle: 'expanded'
          }
        },
        sass: {
          additionalData: `
            @use "styles/themes/${APP_THEME}/_additional.scss" as *
          `,
          sassOptions: {
            outputStyle: 'expanded'
          }
        }
      }
    },
    server: {
      host: '0.0.0.0',
      port: 8080,
      strictPort: true,
      headers: { 'Access-Control-Allow-Origin': '*' },
      watch: {
        usePolling: true
        // aggregateTimeout: 1000,
        // poll: 1000
      },
      hmr: {
        protocol: 'ws',
        host: 'jakomeet.test'
      },
      proxy: VUE_DEV_PROXY
      // proxy: {
      //   target: "nginx", // replace with your web server container
      //   proxyReq: [
      //       function(proxyReq) {
      //           proxyReq.setHeader('HOST', URL + ':3000'); // replace with your site host
      //       }
      //   ]
      // },
    }
  }
})
