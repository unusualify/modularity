const { defineConfig } = require('@vue/cli-service')
const webpack = require('webpack')
const fs = require('fs')
const path = require('path')
const dotenv = require('dotenv')

const env = dotenv.config({ path: path.resolve(__dirname, '../../../../.env') })

const isProd = process.env.NODE_ENV === 'production'

const URL = 'crm.template'
const DEV_ENV = process.env.UNUSUAL_DEV_ENV || 'container'

// Define global vue variables
process.env.VUE_APP_NAME = process.env.VUE_APP_NAME || 'UNUSUAL'
process.env.BASE_URL = process.env.BASE_URL || 'crm.template:8080'

// eslint-disable-next-line no-console
console.log('\x1b[32m', `\n🔥 Building Unusual assets in ${isProd ? 'production' : 'dev'} mode.`)

/**
 * For configuration
 * @see: https://github.com/johnagan/clean-webpack-plugin
 */
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
/**
 * For configuration
 * @see: https://github.com/cascornelissen/svg-spritemap-webpack-plugin
 */
const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin')
/**
 * For configuration
 * @see: https://github.com/webdeveric/webpack-assets-manifest
 */
const WebpackAssetsManifest = require('webpack-assets-manifest')
/**
 * For configuration
 * @see: https://github.com/Turbo87/webpack-notifier
 */
const WebpackNotifierPlugin = require('webpack-notifier')

const BrowserSyncPlugin = require('browser-sync-webpack-plugin')

const { VueLoaderPlugin } = require('vue-loader')

const srcDirectory = 'src'
const partialsDirectory = '../../src/views/partials'
const outputDir = isProd ? 'dist' : (process.env.UNUSUAL_DEV_ASSETS_PATH || 'dist')
const assetsDir = process.env.UNUSUAL_ASSETS_DIR || 'unusual'

const pages = {
  'core-form': `${srcDirectory}/js/core-form.js`,
  'core-index': `${srcDirectory}/js/core-index.js`
//   'main-buckets': `${srcDirectory}/js/main-buckets.js`,
//   'main-dashboard': `${srcDirectory}/js/main-dashboard.js`,
//   'main-free': `${srcDirectory}/js/main-free.js`
}

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

const plugins = [
  new CleanWebpackPlugin(),
  new WebpackAssetsManifest({
    output: `${assetsDir}/unusual-manifest.json`,
    publicPath: true,
    customize (entry, original, manifest, asset) {
      const search = new RegExp(`${assetsDir.replace(/\//gm, '\/')}\/(css|fonts|js|icons)\/`, 'gm')
      return {
        key: entry.key.replace(search, '')
      }
    }
  }),
  new webpack.ProvidePlugin({
    $: 'jquery',
    axios: 'axios',
    'window.$': 'jquery',
    'window._': 'lodash'
    // 'window.jQuery': 'jquery'
  }),
  new webpack.DefinePlugin({
    'process.env': JSON.stringify({
      VUE_APP_NAME: process.env.VUE_APP_NAME,
      ...env.parsed
    }),
    __VUE_I18N_FULL_INSTALL__: JSON.stringify(true),
    __VUE_I18N_LEGACY_API__: JSON.stringify(true),
    __INTLIFY_PROD_DEVTOOLS__: JSON.stringify(true)

  })
//   new VueLoaderPlugin()
  //   new SVGSpritemapPlugin(`${srcDirectory}/icons/**/*.svg`, svgConfig()),
  //   new SVGSpritemapPlugin(`${srcDirectory}/icons-files/**/*.svg`, svgConfig('files')),
  //   new SVGSpritemapPlugin(`${srcDirectory}/icons-wysiwyg/**/*.svg`, svgConfig('wysiwyg')),
]

if (!isProd) {
  plugins.push(
    new WebpackNotifierPlugin({
      title: 'Unusual',
      contentImage: path.join(__dirname, 'docs/.vuepress/public/favicon-180.png')
    })
  )
  plugins.push(
    new webpack.HotModuleReplacementPlugin({
      // Options...
    })
  )
}

const devServer = {
  devMiddleware: {
    index: false // specify to enable root proxying
  },
  host: '0.0.0.0',
  allowedHosts: 'all',
  headers: { 'Access-Control-Allow-Origin': '*' },
  compress: true,
  hot: true,
  static: {
    publicPath: '/unusual',
    watch: {
      ignored: '/node_modules/',
      usePolling: true,
      aggregateTimeout: 1000,
      poll: 1000
    }
  },
  proxy: 'http://nginx',
  client: {
    webSocketURL: 'ws://crm.template:8080/ws'
  }

}
if (DEV_ENV === 'container') {
  plugins.push(
    new BrowserSyncPlugin(
      {
        open: false,

        // host: '0.0.0.0',
        port: 8080,

        // proxy: 'http:/localhost:8080',
        proxy: {
          target: 'nginx', // replace with your web server container
          proxyReq: [
            function (proxyReq) {
              proxyReq.setHeader('HOST', URL + ':8080') // replace with your site host
            }
          ]
        },

        files: [
          'app/**/*.php',
          'resources/views/**/*.php',
          'public/**/*.(js|css|json)'
        ],
        snippetOptions: {
          rule: {
            match: /(<\/body>|<\/pre>)(?!.*(<\/body>|<\/pre>))/is,
            fn: function (snippet, match) {
              return snippet + match
            }
          }
        }
      },
      {
        // prevent BrowserSync from reloading the page
        // and let Webpack Dev Server take care of this
        reload: false
      }
    )
  )
} else {
//   devServer.hot = true
}

// Define npm module resolve order: 1. local (Twill), 2. root (App)
const appModuleFolder = path.resolve(__dirname, '../../../../node_modules') // vendor/area17/twill/
const resolveModules = ['node_modules']
if (fs.existsSync(appModuleFolder)) {
  resolveModules.push(appModuleFolder)
}

module.exports = defineConfig({
//   transpileDependencies: [
//     'vuetify'
//   ],
  // Define base outputDir of build
  outputDir,
  // Define root asset directory
  assetsDir,
  // Remove sourcemaps for production
  productionSourceMap: false,
  css: {
    extract: false,
    loaderOptions: {
      // define global settings imported in all components
      sass: {
        additionalData: `
            @import "styles/setup/_settings.scss";
        `
      }
    }
  },
  // Define entries points
  pages,
  //   devServer,
  runtimeCompiler: true,
  configureWebpack: {
    resolve: {
      alias: {
        // vue$: path.join(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js'),
        // vue: 'vue/dist/vue.esm-bundler.js',
        // vue$: '@vue/runtime-dom',
        'vue-template-compiler$': '~/vue-template-compiler/build.js',
        'prosemirror-tables': path.join(__dirname, 'node_modules/prosemirror-tables/src/index.js'),
        'prosemirror-state': path.join(__dirname, 'node_modules/prosemirror-state/src/index.js'),
        'prosemirror-view': path.join(__dirname, 'node_modules/prosemirror-view/src/index.js'),
        'prosemirror-transform': path.join(__dirname, 'node_modules/prosemirror-transform/src/index.js')
      },
      modules: resolveModules
    },
    plugins,
    performance: {
      hints: false
    },
    watchOptions: {
      poll: true,
      ignored: /node_modules/
    },
    devServer,
    externals: {
    //   vue: 'Vue'
    }

  },
  chainWebpack: config => {
    // Update default vue-cli aliases
    // console.log(config.module.rules)
    config.resolve.alias.set('fonts', path.resolve(`${srcDirectory}/fonts`))
    config.resolve.alias.set('@', path.resolve(`${srcDirectory}/js`))
    config.resolve.alias.set('styles', path.resolve(`${srcDirectory}/sass`))
    config.resolve.alias.set('__components', path.resolve(`${srcDirectory}/js/components`))
    config.resolve.alias.set('__layouts', path.resolve(`${srcDirectory}/js/components/layouts`))
    config.resolve.alias.set('__setup', path.resolve(`${srcDirectory}/js/setup`))

    config.resolve.alias.set('~', path.resolve(__dirname, 'node_modules'))
    // config.resolve.alias.set('vue', 'vue/dist/vue.esm-bundler.js')
    // config.resolve.alias.set('vue', '@vue/runtime-dom')

    // Delete HTML related webpack plugins by page
    Object.keys(pages).forEach(page => {
      config.plugins.delete(`html-${page}`)
      config.plugins.delete(`preload-${page}`)
      config.plugins.delete(`prefetch-${page}`)
    })

    config.module
      .rule('vue')
      .use('vue-loader')
      .tap(options => {
        // options.compilerOptions = {
        //   isCustomElement: tag => tag.startsWith('ue-')
        // }
        // modify the options...
        // console.log(options)
        return {
          ...options
        //   compilerOptions: {
        //     compatConfig: {
        //       MODE: 2
        //     }
        //   }
        }
      })

    // config.module
    //   .rule('fonts')
    //   .test(/\.(ttf|otf|eot|woff|woff2)$/)
    //   .use('file-loader')
    //   .loader('file-loader')
    //   .tap(options => {
    //     console.log(options)
    //     options = {
    //       // limit: 10000,
    //       name: '/unusual/fonts/[name].[ext]'
    //     }
    //     return options
    //   })
    //   .end()
  }

})

// module.exports = config
