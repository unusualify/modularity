<template>
  <v-app id="inspire" :style="{background: $vuetify.theme.themes.dark.background}">
      <Sidebar :items="sidebarItems" />
      <!-- <SidebarRight /> -->

  </v-app>
</template>

<script>
    import Sidebar from './Sidebar.vue';
    export default {
        name: 'Home',
        data() {
            return {

                sidebarItems: [
                    {icon: 'fas fa-plus'},
                    {icon: 'fas fa-th-large'},
                    {icon: 'fas fa-align-center'},
                    {icon: 'fas fa-gitter'},
                    {icon: 'fas fa-chart-line'},
                ]
            }
        },

        components: {
            Sidebar
        }

    }
</script>

<style>

</style>
