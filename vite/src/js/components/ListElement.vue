<template>
    
    <v-list-item 
        v-if="!item.items" 
        active-class="sidebar-item-active" 
        :ripple="false" 
        :href="item.link" 
        :append="false"
        > 

        <v-list-item-icon>

            <!-- <v-icon v-text="item.icon"> </v-icon> -->
            <v-icon> {{item.icon}} </v-icon>

        </v-list-item-icon>

        <v-list-item-content>
            <v-list-item-title v-text="item.text"></v-list-item-title>
        </v-list-item-content>
        
    </v-list-item>

    <v-list-group
        v-else
        :value="false"
        :ripple="true"
        :sub-group="false"
        :prepend-icon="item.icon"
        v-model="activeItem"
        active-class="sidebar-item-active" 
      >
        <template v-slot:activator>

            <v-list-item-content>
                <v-list-item-title v-text="item.text"></v-list-item-title>
            </v-list-item-content>
                
        </template>

        <v-list-item-group v-model="activeSubItem">

            <v-list-item
                v-for="(subItem, k) in item.items"
                :key="k"
                :href="subItem.link"
                link
                active-class="sidebar-sub-item-active"
                style="margin-left:40px;"
            >
                <v-list-item-icon>

                    <!-- <v-icon v-text="item.icon"> </v-icon> -->
                    <v-icon> {{subItem.icon}} </v-icon>

                </v-list-item-icon>

                <v-list-item-content>
                    <!-- <router-link :to="subItem.action"> {{ subItem.text }}</router-link> -->
                    <v-list-item-title v-text="subItem.text" ></v-list-item-title>
                </v-list-item-content>
            </v-list-item>

        </v-list-item-group>
    </v-list-group>
</template>

<script>
export default {
    name: "ue-list-element",
    props: {
        item: {
            type: Object,
            required: true,
        },
        selectItem: {
            type: Number,
            default: 0,
        },
        selectedSubItem: {
            type: Number,
            default: 0
        }
    },
    data: function() {
        return {
            // selectItem: 0,
            activeSubItem: this.selectedSubItem,
            activeItem: this.selectedItem,
        }
    },

    created() {

    }

}
</script>

<style>
    /* .v-list-item-group .v-list-item-active {
        color: grey;
    } */
</style>