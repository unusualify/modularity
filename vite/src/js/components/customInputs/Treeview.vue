<template>

    <!-- TREEVIEW -->
    <v-treeview
        v-model="input"
        :active.sync="input"
        :open.sync="obj.schema.open"
        v-bind="obj.schema"
        :items="obj.schema.items"

    >  
        <slot></slot>
        <!-- works with #[s]="slotData" " -->
        <!-- <template v-for="s in getInjectedScopedSlots(id, obj)" #[s]="slotData"><slot :name="getKeyInjectSlot(obj, s)" v-bind= "{ id, obj, index,  ...slotData}" /></template> -->
    </v-treeview>
    <!-- END TREEVIEW -->
</template>

<script>
import { CustomInputMixin } from '@/mixins'

export default {
    mixins: [CustomInputMixin],
    name:'ue-custom-input-range',
    
    created() {
        // __log(this)
    },
    computed: {

    }  
}
</script>

