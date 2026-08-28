import _ from 'lodash-es'

/**
 * Prefer compiled schema `formEvents[]`, then legacy pipe `event`.
 */
export const resolveFormEventTokens = (input) => {
  if (!input) {
    return []
  }

  if (Array.isArray(input.formEvents) && input.formEvents.length > 0) {
    return _.uniq(input.formEvents.map(token => String(token)).filter(token => token !== ''))
  }

  if (typeof input.formEvents === 'string' && input.formEvents !== '') {
    return _.uniq(input.formEvents.split('|').filter(token => token !== ''))
  }

  if (typeof input.event === 'string' && input.event !== '') {
    return _.uniq(input.event.split('|').filter(token => token !== ''))
  }

  return []
}

export default resolveFormEventTokens
