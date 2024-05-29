import { defineConfig } from 'vitest/config'
import path from 'path'
import { fileURLToPath, URL } from 'node:url'

import Vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import Vue from '@vitejs/plugin-vue'

const srcDir = fileURLToPath(new URL('src', import.meta.url))

export default defineConfig({
  test: {
    globals: true,
    environment: 'jsdom',
    include: ['**/*.{test,spec,mest}.?(c|m)[jt]s?(x)'],
    setupFiles: ['./vitest-setup/jsdom.js'],
    server: {
      deps: {
        inline: ["vuetify"],

        optimizer: {
          web: {
            include: ['element-plus']
          }
        }
      }
    }
  },
  plugins: [
    Vue({
      template: { transformAssetUrls }
    }),
    Vuetify(),
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

})
