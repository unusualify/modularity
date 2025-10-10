import path from 'path'
import fs from 'fs'

export function getCustomThemes() {
  return fs.readdirSync(path.join(__dirname, 'src/js/config/themes/customs'), {withFileTypes: true})
    .filter(dirent => dirent.name.includes('.js'))
    .map(function(dirent){
      return dirent.name.substring(0, dirent.name.lastIndexOf('.')) || dirent.name
    })
}

export function isCustomTheme(themeName) {
  return getCustomThemes().includes(themeName)
}

const refreshPaths = [
  '../../../../lang/**/*',
  './../../../.env'
  // "app/View/Components/**",
  // "resources/views/**",
  // "resources/lang/**",
  // "lang/**",
  // "routes/**"
]

function resolvePluginConfig (config) {
  if (typeof config === 'undefined') {
    throw new Error('laravel-vite-plugin: missing configuration.')
  }
  if (typeof config === 'string' || Array.isArray(config)) {
    config = { input: config, ssr: config }
  }
  // if (typeof config.input === 'undefined') {
  //   throw new Error('laravel-vite-plugin: missing configuration for "input".')
  // }
  // if (typeof config.publicDirectory === 'string') {
  //   config.publicDirectory = config.publicDirectory.trim().replace(/^\/+/, '')
  //   if (config.publicDirectory === '') {
  //     throw new Error("laravel-vite-plugin: publicDirectory must be a subdirectory. E.g. 'public'.")
  //   }
  // }
  if (typeof config.buildDirectory === 'string') {
    config.buildDirectory = config.buildDirectory.trim().replace(/^\/+/, '').replace(/\/+$/, '')
    if (config.buildDirectory === '') {
      throw new Error("laravel-vite-plugin: buildDirectory must be a subdirectory. E.g. 'build'.")
    }
  }
  if (typeof config.ssrOutputDirectory === 'string') {
    config.ssrOutputDirectory = config.ssrOutputDirectory.trim().replace(/^\/+/, '').replace(/\/+$/, '')
  }
  if (config.refresh === true) {
    config.refresh = [{ paths: refreshPaths }]
  }

  return {
    // input: config.input,
    publicDirectory: config.publicDirectory ?? 'public',
    // buildDirectory: config.buildDirectory ?? 'build',
    // ssr: config.ssr ?? config.input,
    // ssrOutputDirectory: config.ssrOutputDirectory ?? 'bootstrap/ssr',
    refresh: config.refresh ?? false,
    hotFile: config.hotFile ?? path.join(config.publicDirectory ?? 'public', 'modularity.hot'),
    // valetTls: config.valetTls ?? false,
    // transformOnServe: config.transformOnServe ?? ((code) => code)

    transformOnDev: config.transformOnDev ?? ((code) => code),
    transforms: config.transforms ?? [],
    theme: config.theme ?? 'unusual'

  }
}

function resolveDevServerUrl (address, config) {
  const configHmrProtocol = typeof config.server.hmr === 'object' ? config.server.hmr.protocol : null
  const clientProtocol = configHmrProtocol ? configHmrProtocol === 'wss' ? 'https' : 'http' : null
  const serverProtocol = config.server.https ? 'https' : 'http'
  const protocol = clientProtocol ?? serverProtocol
  const configHmrHost = typeof config.server.hmr === 'object' ? config.server.hmr.host : null
  const configHost = typeof config.server.host === 'string' ? config.server.host : null
  const serverAddress = isIpv6(address) ? `[${address.address}]` : address.address
  const host = configHmrHost ?? configHost ?? serverAddress
  const configHmrClientPort = typeof config.server.hmr === 'object' ? config.server.hmr.clientPort : null
  const port = configHmrClientPort ?? address.port

  return `${protocol}://${host}:${port}`
}

function isIpv6 (address) {
  return address.family === 'IPv6' || address.family === 6
}

export default function modularity (config) {
  const pluginConfig = resolvePluginConfig(config)
  let defaultTransforms = []
  let resolvedConfig
  let viteDevServerUrl

  let exitHandlersBound = false

  return {
    name: 'modularity-plugin',
    configureServer (server) {
      server.httpServer?.once('listening', () => {
        const address = server.httpServer?.address()
        const isAddressInfo = (x) => typeof x === 'object'

        if (isAddressInfo(address)) {
          viteDevServerUrl = resolveDevServerUrl(address, server.config)
          defaultTransforms = ['url(', 'url(\'', 'url("']
            .map((trans) => [
              trans + resolvedConfig.base,
              trans + viteDevServerUrl + resolvedConfig.base]
            )
          const viteDevServerFullUrl = (viteDevServerUrl + resolvedConfig.base).replace(/^\/|\/$/g, '') // 'http://jakomeet.test:8080/vendor/modularity'
          fs.writeFileSync(pluginConfig.hotFile, viteDevServerFullUrl)
          // fs.writeFileSync(pluginConfig.hotFile, 'http://jakomeet.test:8080/vendor/modularity')

          setTimeout(() => {
            // server.config.logger.info('lalala')
          }, 100)
        }
      })

      if (!exitHandlersBound) {
        const clean = () => {
          if (fs.existsSync(pluginConfig.hotFile)) {
            fs.rmSync(pluginConfig.hotFile)
          }
        }
        process.on('exit', clean)
        process.on('SIGINT', process.exit)
        process.on('SIGTERM', process.exit)
        process.on('SIGHUP', process.exit)
        exitHandlersBound = true
      }
    },
    configResolved (config) {
      resolvedConfig = config
    },
    transform: (code, id) => {
      let transformedCode

      if (/modularity\/vue\/src\/js\/config\/themes\/index.js$/g.test(id)) {
        transformedCode = code
        getCustomThemes().forEach(function(themeName){
          transformedCode += `\r\nexport {default as ${themeName}} from './customs/${themeName}'`
        })
      }

      if (/modularity\/vue\/src\/js\/plugins\/vuetify.js$/g.test(id)) {
        const appThemeFolder = isCustomTheme(pluginConfig.theme) ? `customs/${pluginConfig.theme}` : `${pluginConfig.theme}`
        const importThemeStatement = `import 'styles/themes/${appThemeFolder}/main.scss'`

        transformedCode = code.replaceAll('@/config/themes\'', `@/config/themes'\r\n${importThemeStatement}`)
      }

      if (resolvedConfig.command === 'serve') { // means development
        transformedCode = transformedCode ?? code
        transformedCode = transformedCode.replace(/__modularity_vite_placeholder__/g, viteDevServerUrl)

        const transforms = defaultTransforms.concat(pluginConfig.transforms)

        transforms.forEach((transform) => {
          if (code.includes(transform[0])) {
            transformedCode = transformedCode.replaceAll(transform[0], transform[1])
          }
        })

        transformedCode = pluginConfig.transformOnDev(transformedCode, viteDevServerUrl)
      }

      if (transformedCode) {
        return {
          code: transformedCode,
          map: null
        }
      }
    }
  }
}
