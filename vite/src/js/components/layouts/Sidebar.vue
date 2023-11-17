<template>
    <v-navigation-drawer 
        v-model="$root.sidebarToggle" 
        app 
        color="white"

        :expand-on-hover="$root.isHoverable"
        :mini-variant="$root.isMini"
        
        :mini-variant.sync="$root.miniStatus"
        @update:mini-variant="miniChanging"
        >

        <v-avatar class="d-block text-center mx-auto mt-5">
            <v-icon color="green darken-2" large>
                fab fa-atlassian
            </v-icon>
        </v-avatar>
        
        <v-divider class="mx-3 my-5"></v-divider>

        <v-list dense class="mt-5 d" nav shaped>
            
            <ue-list-group
                :items="items"
                >
            </ue-list-group>

        </v-list>

        

        <div style="position:absolute; bottom: 80px; margin-left: auto; margin-right: auto; left:0; right:0; text-align:center;">
            <v-btn icon :ripple="false">
                <v-badge bordered overlap color="green" dot>
                    <v-icon>far fa-belief </v-icon>
                </v-badge>
                <br>
                <v-avatar size="30">
                    <v-img src="https://cdn.vuetifyjs.com/images/lists/2.jpg">

                    </v-img>
                </v-avatar>
            </v-btn>
        </div>

        <div style="position:absolute; bottom: 20px; margin-left: auto; margin-right: auto; left:0; right:0; text-align:center;">
            <ue-logout-dialog :csrf="$root.csrf" />
        </div>


    </v-navigation-drawer>
</template>

<script>
export default {
    props: {
        items: {
            type: Array,
            required: true,
        },
        rating: {
            type: Number,
            default: 0,
        }
    },
    data() {
        return {
            dialog: false,

            // isMini: this.mini,
        }
    },

    created() {
    },

    beforeCreate(){
        // __log('beforeCreate mini', this.mini)
    },
    
    mounted() {

    },
    watch: {
        
    },

    computed: {

    },  
    methods: {
        onChange(event) {
            console.log('sidebar onChange', event.target.value)
        },
        addItem(item){
            // console.log(this.items)
            this.items.push(item);
        },

        miniChanging(val){
            // __log(
            //     'mini changing', 
            //     val
            // )
        },
    }
}
</script>

<style>
    .border{
        padding-left: 12px;
        /* margin-right: 12px; */
        background: #97ffff;
        border-radius: 10%;
        text-decoration: none;
    }

    .v-list-item-group .v-list-item-active {
        color: grey;
    }
</style>