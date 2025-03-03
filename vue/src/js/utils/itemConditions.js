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
 * Evaluates if an item meets all specified conditions
 * @param {Object} item - The item to check conditions against
 * @param {Array} conditions - Array of condition triplets [path, operator, value]
 * @returns {boolean} True if all conditions are met, false otherwise
 */
export const checkItemConditions = (item, conditions) => {
  if (!conditions || !Array.isArray(conditions) || conditions.length === 0) {
    return true;
  }

  return conditions.every(condition => {
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
      default:
        console.warn(`Unknown operator: ${operator}`);
        return false;
    }
  });
};
