/**
 * Shared Sass options for Vite and Vitest.
 * Uses the modern compiler API (Dart Sass 2.x ready) via sass-embedded.
 */
export const sassApi = 'modern-compiler'

/** Sass language deprecations from Vuetify/deps — not the legacy JS API. */
export const sassSilenceDeprecations = [
  'import',
  'global-builtin',
  'color-functions',
  'slash-div',
  'if-function',
  'null-alpha',
  'function-units',
]

export function createSassPreprocessorOptions ({ themeFolder, style = 'expanded' }) {
  return {
    scss: {
      api: sassApi,
      additionalData: `
        @use "styles/themes/${themeFolder}/_additional.scss" as *;
      `,
      quietDeps: true,
      silenceDeprecations: sassSilenceDeprecations,
      style,
    },
    sass: {
      api: sassApi,
      additionalData: `
        @use "styles/themes/${themeFolder}/_additional.scss" as *
      `,
      quietDeps: true,
      silenceDeprecations: sassSilenceDeprecations,
      style,
    },
  }
}
