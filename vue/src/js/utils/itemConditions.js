import { isObject, omit } from 'lodash-es';

/**
 * Helper method to get nested object values using dot notation
 * @param {Object} obj - The object to extract values from
 * @param {string} path - Dot notation path to the desired property
 * @returns {*} The value at the specified path or undefined if not found
 */
export const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, part) => {
    return current && current[part] !== undefined ? current[part] : undefined;
  }, obj);
};

/**
 * Evaluates a single condition against an item
 * @param {Array} condition - Single condition [path, operator, value, logicalOperator?]
 * @param {Object} item - The item to check the condition against
 * @returns {boolean} True if condition is met, false otherwise
 */
export const evaluateCondition = (condition, item) => {
  const [path, operator, value] = condition;
  let actualValue = getNestedValue(item, path);

  // Handle array length for comparison operators
  if (['>', '<', '>=', '<=', '=', '=='].includes(operator) && Array.isArray(actualValue)) {
    actualValue = actualValue.length;
  }

  switch (operator) {
    case '=':
    case '==':
      return actualValue === value;
    case '!=':
      return actualValue !== value;
    case '>':
      return actualValue > value;
    case '<':
      return actualValue < value;
    case '>=':
      return actualValue >= value;
    case '<=':
      return actualValue <= value;
    case 'in':
      return Array.isArray(value) && value.includes(actualValue);
    case 'not in':
      return Array.isArray(value) && !value.includes(actualValue);
    case 'exists':
      return actualValue !== undefined && actualValue !== null;
    case 'not exists':
      return actualValue === undefined || actualValue === null;
    default:
      console.warn(`Unknown operator: ${operator}`);
      return false;
  }
};

/**
 * Evaluates if an item meets all specified conditions with support for complex nested logic
 *
 * Supports multiple condition formats:
 * 1. Simple conditions: [path, operator, value]
 * 2. Conditions with logical operator: [path, operator, value, 'or'|'and']
 * 3. Nested condition groups: { operator: 'and'|'or', conditions: [...] }
 * 4. Mixed arrays with nested groups
 *
 * Examples:
 * - Simple AND: [['field1', '=', 'value1'], ['field2', '>', 5]]
 * - Mixed with OR: [['field1', '=', 'value1'], ['field2', '>', 5, 'or'], ['field3', 'exists']]
 * - Nested groups: [
 *     { operator: 'or', conditions: [['field1', '=', 'A'], ['field1', '=', 'B']] },
 *     ['field2', '>', 0]
 *   ]
 * - Complex nested: [
 *     {
 *       operator: 'or',
 *       conditions: [
 *         { operator: 'and', conditions: [['field1', '=', 'A'], ['field2', '>', 5]] },
 *         { operator: 'and', conditions: [['field1', '=', 'B'], ['field3', 'exists']] }
 *       ]
 *     }
 *   ]
 *
 * @param {Array|Object} conditions - Conditions to evaluate
 * @param {Object} haystack - The haystack to check conditions against
 * @returns {boolean} True if all conditions are met, false otherwise
 */
export const checkItemConditions = (conditions, haystack) => {
  if (!haystack || !window.__isObject(haystack) || !conditions) {
    return true;
  }

  // Handle single condition group object
  if (window.__isObject(conditions) && conditions.operator && conditions.conditions) {
    return evaluateConditionGroup(conditions, haystack);
  }

  // Handle array of conditions
  if (!Array.isArray(conditions) || conditions.length === 0) {
    return true;
  }

  return evaluateConditionArray(conditions, haystack);
};


/**
 * Handles item conditions
 * @param {Array|Object} conditions - Conditions to evaluate
 * @param {Object} haystack - The haystack to check conditions against
 * @param {Object} item - The item to manipulate
 * @returns {Object} The item if conditions are met, false otherwise
 */
export const handleItemConditions = (conditions, haystack, item) => {
  handleDisabledTextConditions(haystack, item)

  if (!haystack || !window.__isObject(haystack) || !conditions) {
    return item;
  }

  let condition = true

  if (window.__isObject(conditions) && conditions.operator && conditions.conditions) { // Handle single condition group object
    condition = evaluateConditionGroup(conditions, haystack)
  } else if (!Array.isArray(conditions) || conditions.length === 0) { // Handle array of conditions
    condition = true
  } else {
    condition = evaluateConditionArray(conditions, haystack)
  }

  if(item && isObject(item) && item.readonlyOnCondition) {
    if(!condition) {
      let previousReadonly = Object.prototype.hasOwnProperty.call(item, '_originalReadonly')
        ? item._originalReadonly
        : Object.prototype.hasOwnProperty.call(item, 'readonly')
          ? item.readonly
          : false
      item._originalReadonly = previousReadonly
      item.readonly = true
      condition = true
    }else if(Object.prototype.hasOwnProperty.call(item, '_originalReadonly')) {
      item.readonly = item._originalReadonly
    }

  } else if(item && isObject(item) && item.disabledOnCondition) {

    if(!condition) {
      let previousDisabled = Object.prototype.hasOwnProperty.call(item, '_originalDisabled')
        ? item._originalDisabled
        : Object.prototype.hasOwnProperty.call(item, 'disabled')
          ? item.disabled
          : false

      item._originalDisabled = previousDisabled
      item.disabled = true
      condition = true

    }else if(Object.prototype.hasOwnProperty.call(item, '_originalDisabled')) {
      item.disabled = item._originalDisabled
    }
  }

  return condition ? item : false;
};

/**
 * Resolves item.disabledText (and item.disabledTextColor) from conditional notices
 *
 * Supports two shapes:
 * - single notice: item.disabledText + item.disabledTextConditions
 *   e.g. show a read-only notice on a chat only for closed tickets
 * - multiple notices: item.disabledTexts = [{ text, color?, conditions }]
 *   the first notice whose conditions are met wins
 *   e.g. a success notice for closed tickets, a warning notice for pending ones
 *
 * @param {Object} haystack - The haystack to check conditions against
 * @param {Object} item - The item to manipulate
 */
export const handleDisabledTextConditions = (haystack, item) => {
  if (!item || !isObject(item) || (!item.disabledTextConditions && !Array.isArray(item.disabledTexts))) {
    return
  }

  if (!Object.prototype.hasOwnProperty.call(item, '_originalDisabledText')) {
    item._originalDisabledText = item.disabledText ?? null
    item._originalDisabledTextColor = item.disabledTextColor ?? null
  }

  const isMet = (conditions) => !!haystack
    && isObject(haystack)
    && checkItemConditions(conditions, haystack)

  let text = null
  let color = item._originalDisabledTextColor

  if (item.disabledTextConditions && isMet(item.disabledTextConditions)) {
    text = item._originalDisabledText
  } else if (Array.isArray(item.disabledTexts)) {
    const notice = item.disabledTexts.find(notice => isObject(notice) && notice.text && isMet(notice.conditions))

    if (notice) {
      text = notice.text
      color = notice.color ?? color
    }
  }

  item.disabledText = text

  if (color) {
    item.disabledTextColor = color
  } else {
    delete item.disabledTextColor
  }
}

/**
 * Evaluates a condition group object
 * @param {Object} group - Condition group with operator and conditions
 * @param {Object} item - The item to check conditions against
 * @returns {boolean} Result of the condition group evaluation
 */
export const evaluateConditionGroup = (group, item) => {
  const { operator = 'and', conditions = [] } = group;

  if (!Array.isArray(conditions) || conditions.length === 0) {
    return true;
  }

  const results = conditions.map(condition => {
    // Handle nested condition groups
    if (window.__isObject(condition) && condition.operator && condition.conditions) {
      return evaluateConditionGroup(condition, item);
    }

    // Handle regular conditions
    if (Array.isArray(condition)) {
      return evaluateCondition(condition, item);
    }

    console.warn('Invalid condition format:', condition);
    return false;
  });

  return operator === 'or' ? results.some(Boolean) : results.every(Boolean);
};

/**
 * Evaluates an array of conditions with support for logical operators
 * @param {Array} conditions - Array of conditions
 * @param {Object} item - The item to check conditions against
 * @returns {boolean} Result of the condition array evaluation
 */
export const evaluateConditionArray = (conditions, item) => {
  if (conditions.length === 0) {
    return true;
  }

  let result = null;
  let currentOperator = 'and'; // Default to AND operation

  for (let i = 0; i < conditions.length; i++) {
    const condition = conditions[i];
    let conditionResult;

    // Handle nested condition groups
    if (window.__isObject(condition) && condition.operator && condition.conditions) {
      conditionResult = evaluateConditionGroup(condition, item);
    }
    // Handle regular condition arrays
    else if (Array.isArray(condition)) {
      conditionResult = evaluateCondition(condition, item);

      // Check if this condition has a logical operator for the NEXT operation
      if (condition.length > 3 && ['and', 'or'].includes(condition[3])) {
        // This will be used for the next iteration
        var nextOperator = condition[3];
      }
    } else {
      console.warn('Invalid condition format:', condition);
      conditionResult = false;
    }

    // Apply the logical operation
    if (result === null) {
      // First condition
      result = conditionResult;
    } else {
      if (currentOperator === 'or') {
        result = result || conditionResult;
      } else {
        result = result && conditionResult;
      }
    }

    // Set the operator for the next iteration
    if (typeof nextOperator !== 'undefined') {
      currentOperator = nextOperator;
      nextOperator = undefined;
    } else {
      currentOperator = 'and'; // Default back to AND
    }

    // Short-circuit optimization
    if (currentOperator === 'and' && !result) {
      return false; // No need to continue if AND chain is broken
    }
    if (currentOperator === 'or' && result) {
      // For OR, we need to continue until we find the next AND or end
      let j = i + 1;
      while (j < conditions.length) {
        const nextCond = conditions[j];
        if (Array.isArray(nextCond) && nextCond.length > 3 && nextCond[3] === 'and') {
          break; // Found next AND, stop the OR chain
        }
        if (window.__isObject(nextCond)) {
          break; // Found a condition group, stop the OR chain
        }
        j++;
      }
      if (j >= conditions.length) {
        return true; // OR chain successful and no more conditions
      }
    }
  }

  return result !== null ? result : true;
};

// Legacy function name support - keeping the old function for backward compatibility
export const checkConditions = checkItemConditions;
