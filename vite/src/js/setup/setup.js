import * as changeCase from 'change-case'
import init from './init.js'

// require('./helpers.js')
// require('./init.js')

export default function setup() {
  init()
  window.__globalizeMethods([
    changeCase
  ])
}
