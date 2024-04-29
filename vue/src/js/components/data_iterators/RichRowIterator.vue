<template>
  <v-row>
    <v-col
    cols="6"
    >
      <v-card-title>
        {{"EUROPE" ?? item.name }}
      </v-card-title>
      <v-spacer>
      </v-spacer>


    </v-col>

  </v-row>
</template>

<script>
export default{
  props: {
    item:{
      type: Object
    },
    headers:{
      type: Object
    }
  }
}

</script>
