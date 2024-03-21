export default (eventValue) => {
  const { on, id, key, value, params, obj, data, schema, index, event } = eventValue
  // const { type } = event
  // const { id, _value } = event.target
  // const { key } = event.target.__vnode

  __log(`--------- '${on}' event from '${key}@${id}' with value ${value} ---------------------------------------`)
  // __log(`--------- '${type}' event type from '${key}@${id}' with value ${_value} ---------------------------------------`)
  // __log('--------- \'Event => ', event)
  // __log('--------- \'Event.target => ', event.target)
  // __log('--------- \'Event.target.__vnode => ', event.target.__vnode)

  // console.log(`Key: ${key} | Value: ${value} | Index of Control: ${index}`)
  // console.log(`Key:${key}`)
  // console.log(`Value:`, value)
  __log('current object:', obj)
  __log('model:', data)
  __log('schema:', schema)
  if (params) __log('params:', params)
  if (index) __log('index:', index)
  if (parent) __log('parent:', parent)
  if (event) __log('event:', event)

  // __log('event type:', type)
  // __log('event type:', type)

  return event
}
