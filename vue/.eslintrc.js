module.exports = {
  root: true,
  env: {
    node: true
  },
  extends: [
    'plugin:vue/vue3-essential',
    '@vue/standard'
  ],
  rules: {
    'vue/html-comment-indent': 'off',
    'vue/valid-v-slot': ['error', {
      allowModifiers: true
    }]

    // indent
    // 'indent': ['error', 2, { 'SwitchCase': 1 }],
    // 'vue/script-indent': ['error', 2, {
    //   'baseIndent': 1,
    //   'switchCase': 1
    // }],
    // 'no-useless-escape': 0,
    // // allow paren-less arrow functions
    // 'arrow-parens': 0,
    // // allow async-await
    // 'generator-star-spacing': 0,
    // // allow hasOwnProperty
    // 'no-prototype-builtins': 0,
    // // allow debugger during development
    // 'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0,
    // 'no-console': process.env.NODE_ENV === 'production' ? ['error', { "allow": ["error"] }] : 'off',
  },
  overrides: [
    {
      files: ['*.vue'],
      rules: {
        // 'indent': 'off',
        // 'vue/script-indent': ['error', 2, {
        //   'baseIndent': 1,
        //   'switchCase': 1
        // }]
      }
    },
    {
      files: ['*.vue', '*.js'],
      rules: {
        // 'indent': 'off',
        // 'vue/script-indent': ['error', 2, {
        //   'baseIndent': 1,
        //   'switchCase': 1
        // }]
      },
      globals: {
        __log: 'readable',
        __isString: 'readable',
        __isNumber: 'readable',
        __isObject: 'readable',
        __isset: 'readable',
        __getMethods: 'readable',
        __globalizeMethods: 'readable',
        __responseHandler: 'readable',
        __errorHandler: 'readable',
        __convertArrayOrObject: 'readable'
      }
    }
  ],
  parserOptions: {
    parser: '@babel/eslint-parser',
    requireConfigFile: false
  }
}
