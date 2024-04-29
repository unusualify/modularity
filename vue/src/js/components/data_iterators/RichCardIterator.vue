<template>
    <v-sheet border>
            <v-img
              :gradient="`to top right, rgba(255, 255, 255, .1), rgba(${item.color}, .15)`"
              :src="item.sr ?? 'https://ih1.redbubble.net/image.4905811447.8675/flat,750x,075,f-pad,750x1000,f8f8f8.jpg'"
              height="150"

            >
          </v-img>

            <v-list-item
              :title="item.name"
              density="comfortable"
              lines="one"
            >
              <template v-slot:title>
                <strong class="text-h6">
                  {{ item.name }}
                </strong>
              </template>
            </v-list-item>

            <v-table class="data-iterator-table" density="compact">
              <tbody>
                <tr
                v-for="(header, i) in headers"
                >

                  <th>{{ header.title }}</th>
                  <td>{{ item[header.key] }}</td>

                </tr>
              </tbody>
            </v-table>
          </v-sheet>
</template>

<script>

export default{
  setup(props, context){
    console.log(props)
  },
  props:{
    item:{
      type:Object,
      default:{},
      required: true,
    },
    headers:{
      type:Object
    }
  }
}
</script>
