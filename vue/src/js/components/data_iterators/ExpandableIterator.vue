<template>
  <v-card @click="toggleExpand" color="#EBEFF280" variant="flat" rounded="l" class="mr-0 pr-0">
    <v-row class="d-flex align-center  pr-0 mr-0 ga-0">
      <v-col cols="11">
        <v-card-item>{{ Object.values(item)[0] }}</v-card-item>
      </v-col>
      <v-spacer></v-spacer>
        <v-icon icon="mdi-menu-down" class="pr-3"></v-icon>

    </v-row>

    <v-expand-transition :key="item.id">
      <v-card v-if="isExpanded">
        {{ Object.values(item)[1] }}
      </v-card>
    </v-expand-transition>

  </v-card>
</template>


<script>


 export default{

  props:{

    item:{
      type:Object,
      required:true,
    }
  },
  data(){
    return {
      isExpanded: false
    }
  },
  methods:{
    toggleExpand(){
      this.isExpanded = !this.isExpanded
    }
  }
 }
</script>
