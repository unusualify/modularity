import { find, omitBy, reduce, cloneDeep, map, findIndex, snakeCase, orderBy, get, filter, includes, set, each, isEmpty, unset, omit, pick } from 'lodash-es'

const variablePattern = /\${([^}]+)}\$/g;

const searchInData = (data, pattern) => {
  const results = [];

  // Handle pattern objects and normalize pattern
  if (typeof pattern === 'object' && pattern.pattern) {
    pattern = pattern.pattern;
  }

  // Ensure pattern is a string and handle regex patterns
  if (typeof pattern !== 'string') return results;
  if (pattern.startsWith('regex:')) return results; // Skip regex patterns, handled elsewhere

  const patternParts = pattern.split('.');

  const search = (obj, dataIndex, currentPath = [], depth = 0, parentTitle = null) => {
    if (Array.isArray(obj)) {
      obj.forEach((item, index) => {
        search(item, dataIndex, [...currentPath, index], depth, parentTitle);
      });
    } else if (typeof obj === 'object' && obj !== null) {
      const currentTitle = obj._title || null;
      for (const [key, value] of Object.entries(obj)) {
        const newPath = [...currentPath, key];

        if (key === '_value') {
          search(value, dataIndex, newPath, depth, currentTitle);
        } else if (key === patternParts[depth]) {
          if (depth === patternParts.length - 1) {
            results.push(formatResult(dataIndex, newPath, value, currentTitle, parentTitle));
          } else {
            search(value, dataIndex, newPath, depth + 1, currentTitle);
          }
        }
      }
    }
  };

  data.forEach((item, index) => search(item, index));
  return results;
}

const formatResult = (dataIndex, path, value, title, parentTitle) => {
  const notation = [dataIndex, ...path].join('.');
  const result = { dataIndex, notation, value };

  if (title !== null) {
    result.title = title;
  }
  if (parentTitle !== null) {
    result.parentTitle = parentTitle;
  }

  return result;
}

const findMatchingNotations = (data, pattern) => {
  // Handle pattern objects
  if (typeof pattern === 'object' && pattern.pattern) {
    pattern = pattern.pattern;
  }

  // Handle regex patterns
  if (typeof pattern === 'string' && pattern.startsWith('regex:')) {
    const regexStr = pattern.replace('regex:', '')
                           .replace('content.', ''); // Remove content. prefix if exists
    const regex = new RegExp(regexStr);
    const matches = [];

    // Look through the data structure
    for (const item of data) {
      if (item.content) {
        Object.entries(item.content).forEach(([key, value]) => {
          if (regex.test(key)) {
            matches.push({
              key,
              value: value // This will include _title and _value
            });
          }
        });
      }
    }
    return matches;
  }

  // Handle normal patterns
  return searchInData(data, pattern);
}

/**
 * context is an array, string or object
 * if context is an array, each item is a notation
 * if context is an object, it has title and items properties
 * items is an array of notations or items array
 * each item of items array is an array of notations
 * so we need to handle all 3 cases
 * finding the notations is done in findMatchingNotations method
 * output should be an array of objects or object
 * object has title, col, values according to inner structure items or being string of itself
 *
 * for example, for input as following:
 * $samplePreview variable
 *
 * source $data as following:
    $sampleSource variable
  *
  * I need a output as following:
    [
      {
        'title': 'Paketler',
        'items': [
          [
            'Turkey',
            'France'
          ],
        ]
      },
      {
        'title': 'Turkey',
        'items': [
          ['Paket', 'Wire'],
          ['Dağıtım Dili', 'Türkçe, İngilizce']
        ]
      }
      {
        'title': 'France',
        'items': [
          ['Paket', 'Premium'],
          ['Dağıtım Dili', 'Türkçe, İngilizce']
        ]
      },
      {
        'title': 'Paketler',
        'items': [
          ['Wire', 'TRY 2.200,00'],
          ['Premium', 'TRY 1.000,00']
        ]
      },
      {
        'title': 'Press Release Content',
        'items': [
          [
            'I have Press Release',
          ],
          {
            Date: '2024-10-31',
            Time: '12:00',
            'Time Zone': '(UTC+02:00) Istanbul'
          }
        ]
      }
    ]
  *
  * findMatchingNotations method already handles finding values according to _value or related key
  * but we need a more generalized or detailed structure
  */
const formattedPreview = (data, formation) => {
  const formatted = [];

  // Helper function to extract values based on config
  const extractValues = (value, config = {}) => {
    // Default configuration options for value extraction
    const {
      titleKey = '_title',
      valueKey = '_value',
      pickKeys = [],
      mapArrayItems = true,
      nested = false,
      includeTitle = true,
      col = 6                    // Add default col value
    } = config;

    if (!value) return null;

    // Handle array values
    if (Array.isArray(value[valueKey])) {
      if (mapArrayItems) {
        return [
          includeTitle ? value[titleKey] : null,
          value[valueKey].map(item => item[titleKey]),
          col  // Include col in return value
        ].filter(Boolean);
      }
      return [value[titleKey], value[valueKey], col];
    }

    // Handle objects when specific keys need to be picked
    if (pickKeys.length > 0) {
      const picked = pickKeys.map(key => value[key]).filter(Boolean);
      return picked.length ? [value[titleKey], ...picked, col] : null;
    }

    // Default case - return title, value, and col
    return [
      includeTitle ? value[titleKey] : null,
      value[valueKey],
      col
    ].filter(Boolean);
  };

  // Helper to find the parent key of a field in the source data
  const findPrefix = (data, fieldPattern) => {
    if (typeof fieldPattern === 'object' && fieldPattern.pattern) {
      fieldPattern = fieldPattern.pattern;
    }

    const patternParts = parsePattern(fieldPattern);
    if (!patternParts.length) return '';

    if (patternParts[0].startsWith('regex:')) {
      const matches = [];
      for (const item of data) {
        for (const [key, value] of Object.entries(item)) {
          if (typeof value === 'object' && value !== null) {
            const hasField = Object.keys(value).some(k =>
              matchesPattern(patternParts[0], k)
            );
            if (hasField) {
              matches.push(key);
            }
          }
        }
      }
      return matches.length ? matches : '';
    }

    // Original prefix finding logic
    for (const item of data) {
      for (const [key, value] of Object.entries(item)) {
        if (typeof value === 'object' && value !== null) {
          if (patternParts[0] in value) {
            return key;
          }
        }
      }
    }
    return '';
  };

  // Helper to ensure col property exists
  const ensureCol = (item, defaultCol = 6) => {
    if (!item.col) {
      item.col = defaultCol;
    }
    return item;
  };

  // Helper to generate section key
  const generateSectionKey = (index = 0) => `section_${index + 1}`;

  // Add new helper for regex pattern matching
  const matchesPattern = (pattern, key) => {
    if (typeof pattern !== 'string') return false;
    if (pattern.startsWith('regex:')) {
      const regexStr = pattern.replace('regex:', '');
      const regex = new RegExp(regexStr);
      return regex.test(key);
    }
    return pattern === key;
  };

  // Helper to parse pattern path
  const parsePattern = (pattern) => {
    if (typeof pattern === 'object' && pattern.pattern) {
      pattern = pattern.pattern;
    }
    if (typeof pattern !== 'string') return [];

    // For regex patterns, return the full pattern
    if (pattern.startsWith('regex:')) {
      // Extract the content part for regex patterns
      const contentPattern = pattern.includes('content.')
        ? pattern.replace('regex:content.', 'regex:')
        : pattern;
      return ['content', contentPattern];
    }

    return pattern.split('.');
  };

  // Process each formatting instruction
  for (const context of formation) {
    // Handle string patterns or objects with pattern property
    if (typeof context === 'string' || (typeof context === 'object' && context.pattern)) {
      const pattern = typeof context === 'string' ? context : context.pattern;
      const config = {
        col: 6,  // Default col value
        ...typeof context === 'object' ? context : {}
      };
      const isWildcard = pattern.endsWith('.*');

      // Find matching values in data
      const results = findMatchingNotations(data, isWildcard ? pattern.slice(0, -2) : pattern);

      if (results.length > 0) {
        if (isWildcard && results[0].value._value) {
          // Skip the overview section unless specifically requested
          if (!config.includeOverview) {
            // Process individual items directly
            results[0].value._value.forEach((item, index) => {
              const details = Object.entries(item)
                .filter(([key]) => !key.startsWith('_'))
                .map(([_, value]) => {
                  if (Array.isArray(value._value) && config.mapArrayItems === false) {
                    return [value._title, value._value];
                  }
                  return extractValues(value, config);
                })
                .filter(Boolean);

              if (details.length > 0) {
                if (config.outputFormat === 'object') {
                  // Convert array items to object format
                  const itemsObject = {};
                  details.forEach(([title, value]) => {
                    itemsObject[title] = value;
                  });

                  formatted.push(ensureCol({
                    title: item._title,
                    items: config.nested
                      ? { [generateSectionKey(index)]: itemsObject }
                      : itemsObject
                  }, config.col));
                } else {
                  // Default array format
                  formatted.push(ensureCol({
                    title: item._title,
                    items: config.nested ? [details] : details
                  }, config.col));
                }
              }
            });
          } else {
            // Only add overview if specifically requested
            const mainResult = extractValues(results[0].value, {
              ...config,
              mapArrayItems: true
            });

            if (mainResult) {
              formatted.push(ensureCol({
                title: mainResult[0],
                items: [mainResult[1]]
              }, config.col));
            }
          }
        } else {
          // Handle specific patterns
          const items = results.map(result =>
            extractValues(result.value, config)
          ).filter(Boolean);

          if (items.length > 0) {
            formatted.push(ensureCol({ items }, config.col));
          }
        }
      }
    }else if (Array.isArray(context)) { // Handle array patterns
      const items = [];
      const config = { col: 6 };  // Default col for array patterns

      for (const pattern of context) {
        const results = findMatchingNotations(data, pattern);
        results.forEach(r => {
          if (r.value._value) {
            const extracted = extractValues(r.value, config);
            items.push([extracted[0], extracted[1]]);  // Only use title and value
          }
        });
      }
      if (items.length > 0) {
        formatted.push(ensureCol({ items }, config.col));
      }
    }else if (typeof context === 'object' && context.items) {// Handle object patterns with items property
      const result = {
        title: context.title,
        items: context.outputFormat === 'object' ? {} : [],
        col: context.col || 6  // Use provided col or default
      };

      if (context.col) {
        result.col = context.col;
      }

      // Process each item in the items array
      context.items.forEach((item, sectionIndex) => {
        // Handle both array and object patterns in items
        if (typeof item === 'object') {
          if (Array.isArray(item)) {
            // Handle array of patterns
            const itemObject = {};

            for (const pattern of item) {
              const prefix = findPrefix(data, pattern);
              const fullPattern = prefix ? `${prefix}.${pattern}` : pattern;

              const matches = findMatchingNotations(data, fullPattern);
              matches.forEach(match => {
                if (match.value) {
                  const title = match.value._title;
                  const value = match.value._value;
                  if (value) {
                    itemObject[title] = value;
                  }
                }
              });
            }

            if (Object.keys(itemObject).length > 0) {
              if (context.nested) {
                result.items[generateSectionKey(sectionIndex)] = itemObject;
              } else {
                Object.assign(result.items, itemObject);
              }
            }
          } else {
            // Handle object pattern with regex support
            const pattern = item.pattern;

            if (pattern.startsWith('regex:')) {
              const matches = findMatchingNotations(data, pattern);
              const contentValues = {};

              matches.forEach((match) => {
                if (match.value && match.value._value) {
                  if (item.simpleValue) {
                    contentValues[match.key] = match.value._value;
                  } else {
                    contentValues[match.value._title] = match.value._value;
                  }
                }
              });

              if (Object.keys(contentValues).length > 0) {
                if (context.nested) {
                  result.items[generateSectionKey(sectionIndex)] = contentValues;
                } else {
                  Object.assign(result.items, contentValues);
                }
              }
            } else {
              const prefixes = findPrefix(data, pattern);

              if (Array.isArray(prefixes)) {
                // Handle regex matches
                prefixes.forEach((prefix, idx) => {
                  const matches = findMatchingNotations(data, pattern);
                  matches.forEach(match => {
                    // Get the actual value from the data structure
                    const contentValue = data.find(d => d.content)?.[match.key];
                    if (contentValue && contentValue._value) {
                      if (item.simpleValue) {
                        if (context.nested) {
                          result.items[generateSectionKey(idx)] = contentValue._value;
                        } else {
                          result.items[`content_${idx + 1}`] = contentValue._value;
                        }
                      } else {
                        const itemObject = {
                          [contentValue._title]: contentValue._value
                        };
                        if (context.nested) {
                          result.items[generateSectionKey(idx)] = itemObject;
                        } else {
                          Object.assign(result.items, itemObject);
                        }
                      }
                    }
                  });
                });
              } else {
                // Handle object pattern with potential simpleValue
                const pattern = item.pattern;
                const prefix = findPrefix(data, pattern);
                const fullPattern = prefix ? `${prefix}.${pattern}` : pattern;

                const matches = findMatchingNotations(data, fullPattern);
                matches.forEach(match => {
                  if (match.value && match.value._value) {
                    if (item.simpleValue) {
                      // For simpleValue, just store the value directly
                      if (context.nested) {
                        result.items[generateSectionKey(sectionIndex)] = match.value._value;
                      } else {
                        result.items = match.value._value;
                      }
                    } else {
                      // Original behavior
                      const itemObject = {
                        [match.value._title]: match.value._value
                      };
                      if (context.nested) {
                        result.items[generateSectionKey(sectionIndex)] = itemObject;
                      } else {
                        Object.assign(result.items, itemObject);
                      }
                    }
                  }
                });
              }
            }
          }
        } else {
          // Handle string pattern
          const prefix = findPrefix(data, item);
          const fullPattern = prefix ? `${prefix}.${item}` : item;

          const matches = findMatchingNotations(data, fullPattern);
          if (context.outputFormat === 'object') {
            matches.forEach(match => {
              if (match.value._value) {
                const value = match.value._value;
                if (context.nested) {
                  result.items[generateSectionKey(sectionIndex)] = {
                    [match.value._title]: value
                  };
                } else {
                  result.items[match.value._title] = value;
                }
              }
            });
          } else {
            matches.forEach(match => {
              if (match.value._value) {
                result.items.push([match.value._value]);
              }
            });
          }
        }
      });

      if ((Array.isArray(result.items) && result.items.length > 0) ||
          (!Array.isArray(result.items) && Object.keys(result.items).length > 0)) {
        formatted.push(ensureCol(result, context.col));
      }
    }
  }

  return formatted;
}

const formattedSummary = (data, formation) => {
  let formattedSummary = {}

  for(const notation in formation){
    const object = formation[notation];
    const values = findMatchingNotations(data, notation);
    formattedSummary[notation] = Object.assign({}, object, { values });
  }

  return formattedSummary
}

const test = () => {

  // samplePatterns.forEach(pattern => {

    // })

}

// Helper function to recursively replace patterns in an object
export function replacePatternInObject(obj, pattern, replacement) {
  if (!obj || typeof obj !== 'object') return obj;

  return Object.keys(obj).reduce((acc, key) => {
    const value = obj[key];

    if (typeof value === 'string') {
      acc[key] = value.replace(pattern, replacement);
    } else if (Array.isArray(value)) {
      acc[key] = value.map(item => replacePatternInObject(item, pattern, replacement));
    } else if (typeof value === 'object') {
      acc[key] = replacePatternInObject(value, pattern, replacement);
    } else {
      acc[key] = value;
    }

    return acc;
  }, {});
}

export function isMatchingPattern(key) {
  return variablePattern.test(key);
}

export function changeVariablePattern(key, value) {
  return key.replace(variablePattern, value);
}

export function replaceCallback(haystack, match, variable) {
  let variableParts = variable.split('??')
  let variableNames = variableParts[0].split('|')
  let defaultValue = variableParts[1] ?? '';

  for(const variableName of variableNames){
    let res = __data_get(haystack, variableName, false)

    if(res){
      return res
    }
  }
  return defaultValue;
}

export function replacePattern(value, haystack) {
  // Check if the entire value is just a single variable pattern
  const singleVariableMatch = value.match(/^\${([^}]+)}\$$/);
  if (singleVariableMatch) {
    // If the entire value is a single variable, return the actual value (could be array/object)
    const variable = singleVariableMatch[1];
    return replaceCallback(haystack, singleVariableMatch[0], variable);
  }

  // For mixed content or multiple variables, convert non-strings to appropriate string representation
  return value.replace(variablePattern, (match, variable) => {
    const result = replaceCallback(haystack, match, variable);

    // If result is array or object, convert to appropriate string representation
    if (Array.isArray(result)) {
      return result.join(', ');
    } else if (typeof result === 'object' && result !== null) {
      return JSON.stringify(result);
    }

    return result;
  });
}


export function replaceVariablesFromHaystack(obj, haystack) {
  if (!obj || typeof obj !== 'object') return obj;

  return Object.keys(obj).reduce((acc, key) => {
    const value = obj[key];

    if (typeof value === 'string') {
      acc[key] = value.replace(variablePattern, replaceCallback.bind(null, haystack));
    } else if (Array.isArray(value)) {
      acc[key] = value.map(item => replaceVariablesFromHaystack(item, haystack));
    } else if (typeof value === 'object') {
      acc[key] = replaceVariablesFromHaystack(value, haystack);
    } else {
      acc[key] = value;
    }

    return acc;
  }, {});
}

export default {
  findMatchingNotations,
  formattedPreview,
  formattedSummary,
  test,
  replacePatternInObject,
  replaceVariablesFromHaystack
}

const samplePatterns = [
  'pressReleasePackages',
  'pressReleasePackages.package_id.packageFeatures',
  'pressReleasePackages.packageLanguages',
  'content.date',
  'content.time',
  'content.timezone',
  'content.press_release_images',
  'content',
]

const samplePreview = [
  {
    pattern: 'pressReleasePackages.*',
    outputFormat: 'object',  // This will preserve arrays in _value
    nested: true,
    mapArrayItems: false,
  },
  {
    col: 12,
    title: 'Press Release Content',
    nested: true,
    outputFormat: 'object',
    items: [
      [
        'content.date',
        'content.fullname',
        'content.email',
        'content.phone',
      ],
      {
        pattern: 'content.content-type',
        simpleValue: true  // Only affects this pattern
      },
      [
        {
          pattern: 'regex:^\\d+_content$',  // Matches 1_content, 2_content, etc.
          simpleValue: true
        },
        'content.press_release_images',
      ]
    ]
  }
]
const sampleSource = [
  {
    "location": {
      "_title": "Location",
      "_type": "input-radio-group",
      "_value": "Select Your Country"
    },
    "PackageCountry": {
      "_title": "Ülke",
      "_type": "input-checklist",
      "_value": [
        "Turkey",
        "France"
      ]
    }
  },
  {
    "pressReleasePackages": {
      "_title": "Paketler",
      "_type": "input-form-tabs",
      "_value": [
        {
          "_title": "Turkey",
          "_model": 1,
          "_value": {},
          "package_id": {
            "_title": "Paket ",
            "_type": "input-form-tabs",
            "_value": "Wire",
            "packageFeatures": "Ücretsiz Dağıtım, 10 yayın içeriği, SEO kurallarına uygun İngilizce basın bülteni yazımı (max. 400 kelime), Seçilen medya listesine dağıtım, Partner haber sitelerine gönderim",
            "prices": "€ 50,00",
            "price": "€ 50,00"
          },
          "packageLanguages": {
            "_title": "Dağıtım Dili",
            "_type": "input-form-tabs",
            "_value": [
              "Türkçe",
              "İngilizce"
            ]
          }
        },
        {
          "_title": "France",
          "_model": 2,
          "_value": {},
          "package_id": {
            "_title": "Paket ",
            "_type": "input-form-tabs",
            "_value": "Wire",
            "packageFeatures": "Ücretsiz Dağıtım, 10 yayın içeriği, SEO kurallarına uygun İngilizce basın bülteni yazımı (max. 400 kelime), Seçilen medya listesine dağıtım, Partner haber sitelerine gönderim",
            "prices": "€ 46,00",
            "price": "€ 46,00"
          },
          "packageLanguages": {
            "_title": "Dağıtım Dili",
            "_type": "input-form-tabs",
            "_value": [
              "İngilizce",
              "Türkçe"
            ]
          }
        }
      ]
    }
  },
  {
    "content": {
      "_title": "Content",
      "_type": "group",
      "content-type": {
        "_title": "Content-type",
        "_type": "input-radio-group",
        "_value": "I Have A Press Release"
      },
      "date": {
        "_title": "Publication Date",
        "_type": "text",
        "_value": "2024-11-05"
      },
      "fullname": {
        "_title": "Contact Person",
        "_type": "text",
        "_value": "Oğuzhan Bükçüoğlu"
      },
      "email": {
        "_title": "Email",
        "_type": "text",
        "_value": "oguz.bukcuoglu@gmail.com"
      },
      "phone": {
        "_title": "Phone Number",
        "_type": "input-phone",
        "_value": "+90 552 313 08 93"
      },
      "2_content": {
        "_title": "İngilizce Content",
        "_type": "input-filepond",
        "_value": []
      },
      "1_content": {
        "_title": "Türkçe Content",
        "_type": "input-filepond",
        "_value": [
          "Screenshot 2024-11-02 at 23.48.26.png"
        ]
      },
      "press_release_images": {
        "_title": "Medias",
        "_type": "input-filepond",
        "_value": []
      }
    }
  }
]

