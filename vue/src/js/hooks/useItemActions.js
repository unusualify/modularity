// hooks/useItemActions.js
import { toRefs, computed, reactive } from 'vue'
import { useStore } from 'vuex'
import _ from 'lodash-es'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types

import ACTIONS from '@/store/actions'
import { FORM, ALERT } from '@/store/mutations/index'
import api from '@/store/api/form'

import { checkItemConditions } from '@/utils/itemConditions';

export const makeItemActionsProps = propsFactory({
  isEditing: {
    type: Boolean,
    default: false
  },
  actions: {
    type: [Array, Object],
    default: () => []
  }
})

export default function useItemActions(props, context) {
  const store = useStore()

  const editingItem = context.actionItem
    || context.item
    || context.editedItem
    || props.item
    || props.editedItem

  const resolveParamValue = (config) => {
    if (!config.source || !config.find || !config.return) {
      return config;
    }

    const sourceData = editingItem[config.source];
    if (!Array.isArray(sourceData)) {
      return undefined;
    }

    const [findKey, findValue] = config.find;
    const item = sourceData.find(item => item[findKey] === findValue);

    return item ? item[config.return] : undefined;
  }

  const handleRequestAction = (action, endpoint) => {
    if (!endpoint) {
      console.error('Endpoint not specified for request action');
      return;
    }

    const method = action.method?.toLowerCase() || 'post';
    if (!api[method]) {
      console.error('Invalid request method:', method);
      return;
    }

    // Prepare parameters
    const params = {};

    // Process each parameter based on its configuration
    for (const [key, config] of Object.entries(action.params)) {
      if (typeof config === 'object' && config !== null) {
        const value = resolveParamValue(config);
        if (value === undefined) {
          console.error(`Could not resolve parameter value for ${key}`);
          return;
        }
        params[key] = value;
      } else {
        params[key] = config;
      }
    }

    api[method](endpoint, params,
      (response) => {
        // __log('handleRequestAction', response)
        if (response.data.message) {
          store.commit(ALERT.SET_ALERT, {
            message: response.data.message,
            variant: response.data.variant
          });
        }
        context.emit('actionComplete', { action, response });
      },
      (error) => {
        store.commit(ALERT.SET_ALERT, {
          message: error.data?.message || 'Action failed',
          variant: 'error'
        });
      }
    );
  }

  const handleModalAction = (action, endpoint) => {
    // Assuming you have a modal system
    store.commit('SET_MODAL', {
      show: true,
      title: action.label,
      component: 'ue-form',
      props: {
        schema: action.schema,
        actionUrl: endpoint,
        async: true
      },
      on: {
        success: (response) => {
          store.commit(ALERT.SET_ALERT, {
            message: response.message || 'Action completed successfully',
            variant: 'success'
          });
          context.emit('action-complete', { action, response });
        }
      }
    });
  }

  const handleBlankAction = (endpoint) => {
    if (!endpoint) {
      console.error('Endpoint not specified for blank action');
      return;
    }

    window.open(endpoint, '_blank');
  }

  const handleDownloadAction = (endpoint) => {
    if (!endpoint) {
      console.error('Endpoint not specified for download action');
      return;
    }

    // Create a temporary link and trigger download
    const link = document.createElement('a');
    link.href = endpoint;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  const shouldShowAction = (action) => {
    // Base condition for editing/creating
    const baseCondition = props.isEditing ? action.editable : action.creatable;

    // If no conditions defined, return base condition
    if (!action.conditions || !baseCondition) {
      return baseCondition;
    }

    // Check all conditions
    return baseCondition && checkItemConditions(editingItem, action.conditions);
  }

  const flattenedActions = computed(() => window.__isObject(props.actions)
      ? Object.values(props.actions)
      : props.actions
  )

  const states = reactive({
    showedActions: computed(() => flattenedActions.value.filter(action => shouldShowAction(action)))
  })

  const methods = reactive({
    // methods
    shouldShowAction,
    handleAction(action) {
      if (!action.type) {
        console.warn('Action type not specified:', action);
        return;
      }

      // Replace any URL parameters
      const endpoint = action.endpoint?.replace(':id', editingItem.id);

      switch (action.type) {
        case 'request':
          handleRequestAction(action, endpoint);
          break;

        case 'modal':
          handleModalAction(action, endpoint);
          break;

        case 'download':
          handleDownloadAction(endpoint);
          break;

        case 'blank':
          handleBlankAction(endpoint);
          break;

        default:
          console.warn('Unknown action type:', action.type);
      }
    }
  })

  return {
    ...toRefs(states),
    ...toRefs(methods),
    // ...toRefs(states)
  }
}
