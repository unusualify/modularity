import { reduce } from 'lodash-es'

export const isViewOnlyInput = (input) => {
  return Object.prototype.hasOwnProperty.call(input, 'viewOnlyComponent')
}

export const isFormEventInput = (input) => {
  return Object.prototype.hasOwnProperty.call(input, 'isEvent')
    && input.isEvent
    && ( ['select', 'autocomplete', 'combobox', 'switch'].includes(input.type)
      || __isset(input.viewOnlyComponent)
      || input.noSubmit
    )
}

export const getTranslationInputsCount = (inputs) => {
  return getTranslationInputs(inputs).length
}

export const getTranslationInputs = (inputs, acc = []) => {
  return _.reduce(inputs, (acc, input) => {
    if(__isset(input) && __isset(input.schema) && ['wrap', 'group', 'repeater', 'input-repeater'].includes(input.type)){
      acc = getTranslationInputs(input.schema, acc)
    } else if(Object.prototype.hasOwnProperty.call(input, 'translated') && input.translated)
      acc.push(input)

    return acc
  }, acc)
}

export const flattenGroupSchema = (schema, groupName) => {
  return reduce(schema, (acc, value, key) => {
    const newKey = key.split('.').filter(part => part !== groupName).join('.');
    acc[newKey] = value;
    return acc;
  }, {});
};

export const processInputs = (inputObj) => {
  return reduce(inputObj, (acc, value, key) => {
    if (value.type === 'wrap' && value.schema) {
      Object.assign(acc, processInputs(value.schema));
    } else if (value.type === 'groupz' && value.schema) {
      // acc[key] = {
      //   ...value,
      //   schema: processInputs(value.schema)
      // };
    } else if (!value.slotable) {
      acc[key] = value;
    } else {
      acc[key] = value;
    }
    return acc;
  }, {});
};

