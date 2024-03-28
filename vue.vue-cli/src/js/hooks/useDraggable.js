// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { reactive, toRefs, computed } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs' // Types
import htmlClasses from '@/utils/htmlClasses'

export const makeDraggableProps = propsFactory({
  /**
   * Define if the component can be draggable or not
   * @type {Boolean}
   */
  draggable: {
    type: Boolean,
    default: true
  },
  orderKey: {
    type: String,
    default: 'position'
  }
})

// by convention, composable function names start with "use"
export default function useDraggable (props, context) {
  const state = reactive({
    animation: 150,
    handle: '.drag__handle',
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    dragClass: 'sortable-drag',
    scrollSensitivity: 30,

    dragOptions: computed(() => {
      return {
        disabled: !props.draggable,
        animation: state.animation
        // handle: state.handle
        // ghostClass: state.ghostClass,
        // chosenClass: state.chosenClass,
        // dragClass: state.dragClass,
        // scrollSensitivity: state.scrollSensitivity,
      }
    })
  })
  const methods = reactive({

  })

  // const computed =

  // expose managed state as return value
  return {
    // ...toRefs(_props),
    ...toRefs(methods),
    ...toRefs(state)
  }
}
