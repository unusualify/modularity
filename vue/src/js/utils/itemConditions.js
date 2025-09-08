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
 * @param {Object} item - The item to check conditions against
 * @returns {boolean} True if all conditions are met, false otherwise
 */
export const checkItemConditions = (conditions, item) => {
  if (!item || !window.__isObject(item) || !conditions) {
    return true;
  }

  // Handle single condition group object
  if (window.__isObject(conditions) && conditions.operator && conditions.conditions) {
    return evaluateConditionGroup(conditions, item);
  }

  // Handle array of conditions
  if (!Array.isArray(conditions) || conditions.length === 0) {
    return true;
  }

  return evaluateConditionArray(conditions, item);
};

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
