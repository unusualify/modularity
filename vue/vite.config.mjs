// Plugins
import Components from 'unplugin-vue-components/vite'
import Vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import Vue from '@vitejs/plugin-vue'
import Inspect from 'vite-plugin-inspect'
import { viteCommonjs } from '@originjs/vite-plugin-commonjs'
import VitePluginSvgSpritemap from '@spiriit/vite-plugin-svg-spritemap'
import Modularity, {isCustomTheme} from './vite-plugin-modularity'

// Utilities
import { defineConfig, loadEnv, splitVendorChunkPlugin } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import path from 'path'
import fs from 'fs'
import { globSync } from 'glob'

import dotenv from 'dotenv'
import dotenvExpand from 'dotenv-expand'

const laravelRootUpperLevel = (dir) => {
  return Array(dir.split('/').length + 1).fill('..').join('/')
}

const env = dotenv.config({ path: path.resolve(__dirname, '../../../.env') }).parsed // prevent writing to `process.env`
dotenvExpand.expand(env)

const partialsDirectory = path.resolve(__dirname, './views/partials')

const svgConfig = (suffix = null, isProduction = true) => {
  suffix = suffix !== null ? `-${suffix}` : ''

  return {
    injectSVGOnDev: true,
    prefix: 'icon--',
    output: {
      // filename: isProduction ? `icons/icons${suffix}-svg.blade.php` : `icons/icons${suffix}.svg`,
      filename: `icons/svg${suffix}.blade.php`,

      // filename: `dist/icons/icons${suffix}-svg[extname]`,
      // filename: `${svgIconsDirectory}/icons/icons${suffix}-svg.blade.php`,
      // filename: `dist/icons/icons${suffix}-svg.blade.php,`

      name: `icons${suffix}.svg`,
      // view: false,
      use: true

    },
    styles: {
      filename: `~svg-sprite-icons${suffix}.scss`
      // variables: {
      //   sprites: `icons${suffix}-sprites`,
      //   sizes: `icons${suffix}-sizes`,
      //   variables: `icons${suffix}-variables`,
      //   mixin: `icons${suffix}-sprites-mixin`
      // }
    }
  }
}

export default defineConfig(({ command, mode }) => {
  const ENV = loadEnv(mode, process.cwd(), '')
  const envPrefix = ['VUE', 'VITE']
  const isProduction = mode === 'production'

  const vendorDir = ENV.VENDOR_DIR || 'vendor/unusualify/modularity'
  const LARAVEL_ROOT_LEVEL = laravelRootUpperLevel(vendorDir)

  const APP_THEME = ENV.VUE_APP_THEME || 'unusualify'
  const VUE_DEV_PORT = ENV.VUE_DEV_PORT || 5173
  const VUE_DEV_HOST = ENV.VUE_DEV_HOST || 'localhost' // jakomeet.test
  const VUE_DEV_PROXY = ENV.VUE_DEV_PROXY || null

  const srcDir = fileURLToPath(new URL('src', import.meta.url))
  const inputDir = fileURLToPath(new URL(`${srcDir}/js`, import.meta.url))
  const inputPattern = fileURLToPath(new URL(`${inputDir}/core-*.js`, import.meta.url))

  const base = '/vendor/modularity'
  const assetsDir = 'assets'

  const outDir = 'dist/modularity'
  // const outDir = fileURLToPath(new URL(`${LARAVEL_ROOT_LEVEL}/public${base}`, import.meta.url))
  const targetDir = fileURLToPath(new URL(`${LARAVEL_ROOT_LEVEL}/public`, import.meta.url))
  const appDir = fileURLToPath(new URL(`${LARAVEL_ROOT_LEVEL}`, import.meta.url))
  const publicDir = 'public'

  const server = {
    host: '0.0.0.0',
    port: VUE_DEV_PORT,
    strictPort: true,
    headers: { 'Access-Control-Allow-Origin': '*' },
    watch: {
      usePolling: true
      // aggregateTimeout: 1000,
      // poll: 1000
    }
  }

  const APP_THEME_FOLDER = isCustomTheme(APP_THEME) ? `customs/${APP_THEME}` : `${APP_THEME}`

  if (!isProduction) {
    server.hmr = {
      protocol: 'ws',
      host: VUE_DEV_HOST
    }

    if(VUE_DEV_PROXY)
      server.proxy = VUE_DEV_PROXY
    // server.proxy = {
    //   target: "nginx", // replace with your web server container
    //   proxyReq: [
    //       function(proxyReq) {
    //           proxyReq.setHeader('HOST', URL + ':3000'); // replace with your site host
    //       }
    //   ]
    // },
  }

  const ziggyPath = path.resolve(appDir, 'vendor/tightenco/ziggy')
  const hasZiggy = fs.existsSync(ziggyPath)

  return {
    // envDir: path.resolve(__dirname, '../../../.env'),
    // define: {
    //   __HAS_ZIGGY__: JSON.stringify(hasZiggy),
    // },
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
          configFile: 'src/sass/themes/' + APP_THEME_FOLDER + '/_settings.scss'
        }
      }),
      Components(),
      splitVendorChunkPlugin(),
      viteCommonjs(),

      VitePluginSvgSpritemap([
        `./src/sass/themes/${APP_THEME_FOLDER}/icons/**/*.svg`,
        './src/icons/**/*.svg'
      ], svgConfig('theme')),

      // VitePluginSvgSpritemap(`./src/sass/themes/${APP_THEME}/icons/**/*.svg`, svgConfig('theme')),
      // VitePluginSvgSpritemap('./src/icons/**/*.svg', svgConfig()),
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
        '~': fileURLToPath(new URL('./node_modules', import.meta.url)),
        '#': fileURLToPath(new URL(`${appDir}`, import.meta.url)),
      }
    },
    build: {
      // target: 'esnext',
      // target: "ES2022", // <--------- ✅✅✅✅✅✅
      // target: 'es2015',

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
            @use "styles/themes/${APP_THEME_FOLDER}/_additional.scss" as *;
          `,

          sassOptions: {
            outputStyle: isProduction ? 'compressed' : 'expanded'
          }
        },
        sass: {
          additionalData: `
            @use "styles/themes/${APP_THEME_FOLDER}/_additional.scss" as *
          `,
          sassOptions: {
            outputStyle: isProduction ? 'compressed' : 'expanded'
          }
        }
      }
    },
    server
  }
})
