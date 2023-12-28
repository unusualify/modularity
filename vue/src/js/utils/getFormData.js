import { isEmpty, find, filter, omitBy, forOwn, reduce } from 'lodash'

const isArrayable = 'custom-input-treeview|treeview|custom-input-checklist'
const isMediableTypes = 'custom-input-file|custom-input-image'
const isMediableFields = 'files|medias'

/*
* Gather selected items in a selected object (currently used for medias and browsers)
* if a block is passed as second argument, we retrieve selected items namespaced by the block id
* and strip it out from the key to clean things up and make it easier for the backend
*/
const gatherSelected = (selected, block = null) => {
  return Object.assign({}, ...Object.keys(selected).map(key => {
    if (block) {
      if (isBlockField(key, block.id)) {
        return {
          [stripOutBlockNamespace(key, block.id)]: selected[key]
        }
      }
    } else if (!key.startsWith('blocks[')) {
      return {
        [key]: selected[key]
      }
    }
    return null
  }).filter(x => x))
}

export const hydrateSelected = (item, rootState, block = null) => {
  if (!__isset(item.id)) {
    rootState.mediaLibrary.selected = {}
  }
  for (const name in item) {
    if (isMediableFields.includes(name)) {
      if (__isObject(item[name])) {
        for (const lang in item[name]) {
          for (const fieldName in item[name][lang]) {
            const key = `${fieldName}[${lang}]`
            rootState.mediaLibrary.selected[key] = item[name][lang][fieldName]
          }
        }
      } else {
        rootState.mediaLibrary.selected = {}
      }
    }
  }

  // __log('hydrated', item, rootState.mediaLibrary)
}

export const isBlockField = (name, id) => {
  return name.startsWith('blocks[' + id + ']')
}

export const stripOutBlockNamespace = (name, id) => {
  const nameWithoutBlock = name.replace('blocks[' + id + '][', '')
  return nameWithoutBlock.match(/]/gi).length > 1 ? nameWithoutBlock.replace(']', '') : nameWithoutBlock.slice(0, -1)
}

export const buildBlock = (block, rootState) => {
  return {
    id: block.id,
    type: block.type,
    editor_name: block.name,
    // retrieve all fields for this block and clean up field names
    content: rootState.form.fields.filter((field) => {
      return isBlockField(field.name, block.id)
    }).map((field) => {
      return {
        name: stripOutBlockNamespace(field.name, block.id),
        value: field.value
      }
    }).reduce((content, field) => {
      content[field.name] = field.value
      return content
    }, {}),
    medias: gatherSelected(rootState.mediaLibrary.selected, block),
    browsers: gatherSelected(rootState.browser.selected, block),
    // gather repeater blocks from the repeater store module
    blocks: Object.assign({}, ...Object.keys(rootState.repeaters.repeaters).filter(repeaterKey => {
      return repeaterKey.startsWith('blocks-' + block.id)
    }).map(repeaterKey => {
      return {
        [repeaterKey.replace('blocks-' + block.id + '_', '')]: rootState.repeaters.repeaters[repeaterKey].map(repeaterItem => {
          return buildBlock(repeaterItem, rootState)
        })
      }
    }))
  }
}

export const isBlockEmpty = (blockData) => {
  return isEmpty(blockData.content) && isEmpty(blockData.browsers) && isEmpty(blockData.medias) && isEmpty(blockData.blocks)
}

export const gatherRepeaters = (rootState) => {
  return Object.assign({}, ...Object.keys(rootState.repeaters.repeaters).filter(repeaterKey => {
    // we start by filtering out repeater blocks
    return !repeaterKey.startsWith('blocks-')
  }).map(repeater => {
    return {
      [repeater]: rootState.repeaters.repeaters[repeater].map(repeaterItem => {
        // and for each repeater we build a block for each item
        const repeaterBlock = buildBlock(repeaterItem, rootState)

        // we want to inline fields in the repeater object
        // and we don't need the type of component used
        const fields = repeaterBlock.content
        delete repeaterBlock.content
        delete repeaterBlock.type

        // and lastly we want to keep the id to update existing items
        fields.id = repeaterItem.id

        return Object.assign(repeaterBlock, fields)
      })
    }
  }))
}

export const gatherBlocks = (rootState) => {
  const used = { ...rootState.blocks.blocks }
  return Object.keys(used).map(name => {
    return used[name].map(block => {
      block.name = name
      return buildBlock(block, rootState)
    })
  }).flat()
}

export const getFormFields_ = (rootState) => {
  const fields = rootState.form.fields.filter((field) => {
    // we start by filtering out blocks related form fields
    return !field.name.startsWith('blocks[') && !field.name.startsWith('mediaMeta[')
  }).reduce((fields, field) => {
    // and we create a new object with field names as keys,
    // to inline fields in the submitted data
    fields[field.name] = field.value
    return fields
  }, {})

  return fields
}

export const getModalFormFields = (rootState) => {
  const fields = rootState.form.modalFields.filter((field) => {
    // we start by filtering out blocks related form fields
    return !field.name.startsWith('blocks[') && !field.name.startsWith('mediaMeta[')
  }).reduce((fields, field) => {
    // and we create a new object with field names as keys,
    // to inline fields in the submitted data
    fields[field.name] = field.value
    return fields
  }, {})

  return fields
}

export const getFormData_ = (rootState) => {
  const fields = getFormFields(rootState)

  // we can now create our submitted data object out of:
  // - our just created fields object,
  // - publication properties
  // - selected medias and browsers
  // - created blocks and repeaters
  const data = Object.assign(fields, {
    cmsSaveType: rootState.form.type,
    published: rootState.publication.published,
    public: rootState.publication.visibility === 'public',
    publish_start_date: rootState.publication.startDate,
    publish_end_date: rootState.publication.endDate,
    languages: rootState.language.all,
    parent_id: rootState.parents.active,

    medias: gatherSelected(rootState.mediaLibrary.selected),
    browsers: gatherSelected(rootState.browser.selected),
    blocks: gatherBlocks(rootState),
    repeaters: gatherRepeaters(rootState)
  })

  return data
}

export const getSchemaModel_ = (inputs, item = null) => {
  // __log(window[process.env.VUE_APP_NAME].STORE.languages.all)
  const isArrayable = 'custom-input-treeview|treeview|custom-input-checklist'
  const editing = __isset(item) && !!item.id
  const submitting = __isset(item)

  const values = Object.keys(inputs).reduce((a, c) => {
    // default model value
    let value = Object.prototype.hasOwnProperty.call(inputs[c], 'default') ? inputs[c].default : ''
    if (isArrayable.includes(inputs[c].type)) {
      value = submitting ? item[c] : []
    }

    if (__isObject(inputs[c])) {
      if (Object.prototype.hasOwnProperty.call(inputs[c], 'translated')) {
        if (submitting || inputs[c].translated) {
          a[inputs[c].name] = window[process.env.VUE_APP_NAME].STORE.languages.all.reduce(function (map, language) {
            if (submitting) {
              if (Object.prototype.hasOwnProperty.call(item, 'translations')) {
                value = find(item.translations, { locale: language.value })[inputs[c].name]
              }
            }

            map[language.value] = value
            return map
          }, {})
        } else {
          a[inputs[c].name] = value
        }
      } else {
        a[inputs[c].name] = submitting ? item[inputs[c].name] : value
      }
    }
    return a
  }, {})

  return values

  // Object.keys(inputs).reduce((a, c) => (
  //   a[inputs[c].name] = inputs[c].hasOwnProperty('default') ? inputs[c].default : '',
  //   a
  // )
  // , {})
}

export const getModel = (inputs, item = null, rootState = null) => {
  const editing = __isset(item)
  const values = Object.keys(inputs).reduce((fields, k) => {
    const input = inputs[k]
    const name = input.name
    // if (isMediableTypes.includes(input.type)) {
    //   // if (editing) { __log(name, item, input) }
    //   return fields
    // }
    // default model value
    let _default = Object.prototype.hasOwnProperty.call(input, 'default') ? input.default : ''
    // let value = Object.prototype.hasOwnProperty.call(input, 'default') ? input.default : ''
    if (isArrayable.includes(input.type)) {
      _default = []
    }
    const value = editing ? item[name] : _default
    if (__isObject(input)) {
      const languages = window[process.env.VUE_APP_NAME].STORE.languages.all
      if (isMediableTypes.includes(input.type)) {
        if (editing && __isset(item[name])) {
          // __log('mediable', name, item)
          fields[name] = item[name]
        } else {
          fields[name] = { tr: [], en: [] }
        }
      } else if (Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated) { // translations
        fields[name] = languages.reduce(function (map, lang) {
          if (editing) {
            if (Object.prototype.hasOwnProperty.call(item, 'translations')) {
              map[lang.value] = find(item.translations, { locale: lang.value })
                ? find(item.translations, { locale: lang.value })[name]
                : item.translations[name][lang.value]
            } else if (__isObject(value) && __isset(value[lang.value])) {
              map[lang.value] = value[lang.value]
            } else {
              map[lang.value] = value ?? ''
            }
          } else {
            map[lang.value] = value ?? ''
          }
          return map
        }, {})
      } else {
        if (!value &&
          editing &&
          Object.prototype.hasOwnProperty.call(item, 'translations') &&
          Object.prototype.hasOwnProperty.call(item.translations, name)
        ) {
          for (const locale in item.translations[name]) {
            fields[name] = item.translations[name][locale]
          }
        } else {
          fields[name] = value
        }
      }
    }

    return fields
  }, {})

  if (editing) {
    values.id = item.id
  }

  // if (rootState) {
  //   return Object.assign(values, {
  //     medias: gatherSelected(rootState.mediaLibrary.selected)
  //   })
  // }
  if (rootState) {
    // hydrateSelected(item, rootState)
  }
  return values
}

export const getSubmitFormData = (inputs, item = null, rootState = null) => {
  const isArrayable = 'custom-input-treeview|treeview|custom-input-checklist'

  const values = Object.keys(inputs).reduce((fields, k) => {
    const input = inputs[k]
    // if (isMediableTypes.includes(input.type)) {
    //   return fields
    // }

    const name = input.name
    // default model value
    if (!__isset(item[name])) {
      let value = input.default ?? ''
      if (isArrayable.includes(input.type)) {
        value = []
      }

      fields[name] = value

      return fields
    }
    const value = item[name]

    if (__isObject(input)) {
      if (Object.prototype.hasOwnProperty.call(input, 'translated')) { // translations
        fields[name] = window[process.env.VUE_APP_NAME].STORE.languages.all.reduce(function (map, lang) {
          if (__isObject(value)) {
            map[lang.value] = __isset(value[lang.value]) ? value[lang.value] : ''
          } else {
            map[lang.value] = value
          }
          return map
        }, {})
      } else {
        fields[name] = item[name]
      }
    }

    return fields
  }, {})

  if (item.id) {
    values.id = item.id
  }

  if (rootState) {
    return Object.assign(values, {
      // medias: gatherSelected(rootState.mediaLibrary.selected)
    })
  }

  return values
}

export const getSchema = (inputs) => {
  const _inputs = omitBy(inputs, (value, key) => {
    return Object.prototype.hasOwnProperty.call(value, 'slotable')
  })

  if (find(_inputs, (input) => Object.prototype.hasOwnProperty.call(input, 'group'))) {
    // reduce(_inputs, (acc, input, key) => {
    //   if(Object.prototype.hasOwnProperty.call(input, 'group')){
    //     if(acc[input.group])
    //   }else{
    //     acc[key] = input
    //   }
    //   return acc
    // }, {})
  }

  return _inputs
}
