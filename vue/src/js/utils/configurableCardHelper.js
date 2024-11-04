export function generateConfigurableCardData(config, data) {
  const result = {
    title: evaluateTemplateString(config.title, data),
    items: []
  };

  for (const [sectionKey, sectionConfig] of Object.entries(config.items || {})) {
    result.items[sectionKey] = {};
    for (const [itemKey, itemConfig] of Object.entries(sectionConfig)) {
      result.items[sectionKey][itemKey] = evaluateExpression(itemConfig, data);
    }
  }
  // __extract(data)
  // __log(window)
  const index = data.index;
  const schemas = data.schemas;
  const expression = "${__data_get(data, 'schemas.1.pressReleasePackages.items[0].name','EUROPE')}"
  // __log(`${schemas.1.pressReleasePackages.items[0].name || 'EUROPE'}`)
  // __log( `with(data) { return \`${expression}\`; }`)
  const func = new Function('data', `return \`${expression}\`;`);
  // const func = new Function('data', `__extract(data);return __log(schemas);`);
  __log(
    `__extract(data);return \`${expression}\`;`,
  )
  __log(func(data))
  return result;
}

function evaluateTemplateString(template, context) {
  // Helper function to get nested property
  function getNestedProperty(obj, path) {
    return path.split('.').reduce((acc, part) => {
      if (acc === undefined) return undefined;
      return acc[part];
    }, obj);
  }

  // Handle the entire expression
  function evaluateExpression(expr) {
    const parts = expr.split('||').map(part => part.trim());
    for (const part of parts) {
      const result = evaluatePart(part);
      if (result !== undefined) return result;
    }
    // If all parts are undefined, return the last part without quotes
    return parts[parts.length - 1].replace(/^['"](.+)['"]$/, '$1');
  }

  // Handle each part of the expression
  function evaluatePart(part) {
    // Replace ${...} with their evaluated results
    part = part.replace(/\$\{([^}]+)\}/g, (match, expr) => {
      return evaluatePart(expr);
    });

    // Handle array indexing
    part = part.replace(/\[([^\]]+)\]/g, (match, index) => {
      return `[${evaluatePart(index)}]`;
    });

    // Evaluate the resulting expression
    try {
      // __log(context, part)
      return getNestedProperty(context, part);
    } catch (error) {
      return undefined;
    }
  }
  // Main function body
  return template.replace(/^\$\{(.+)\}$/g, (match, expr) => {
    return evaluateExpression(expr);
  });
}
