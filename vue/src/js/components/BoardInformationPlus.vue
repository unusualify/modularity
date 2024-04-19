<template>
  <v-card :color="container.color" density="comfortable" :elevation="container.elevation" :class="container.class">
    <v-card-title style="font-weight: bolder"> {{ $t("AT A GLANCE") }}</v-card-title>
    <v-card-item class="px-5">
      <v-row>
        <v-col
        v-for="(card, i) in cards"
        :key="i"
        :cols="card.flex"
        class="px-6"
        >
          <v-card :variant="cardAttribute.variant" :style="{'border-radius': cardAttribute.borderRadius}" :border="cardAttribute.border">
            <v-card-title align="center" :class="cardAttribute.titleClass" :style="{'color' : cardAttribute.titleColor}">
              {{ $t(card.title) }}
            </v-card-title>
            <v-card-item align="center" :class="cardAttribute.infoClass" :style="{'color' : cardAttribute.infoColor}">{{ card.data }}</v-card-item>
          </v-card>
        </v-col>
      </v-row>
    </v-card-item>
  </v-card>
</template>

<script>
export default {
  props:{
    cards: {
      type: Array,
      default: [
      {
        title: 'Active Webinar',
        data: '12',
        flex: 6,
      },
      {
        title: 'Speaker Moderators',
        data: '3',
        flex: 6,
      },
      {
        title: 'Company',
        data: '5',
        flex: 6,
      },
      {
        title: 'Published Webinars',
        data: '7',
        flex: 6,
      },
      ],
    },
    container:{
      type: Object,
      default:{
        color : '#F8F8FF',
        elevation : 10,
        class : 'px-5 py-5'
      }
    },
    cardAttribute: {
      type: Object,
      default: {
        variant : 'outlined',
        borderRadius : 14,
        border : 'md',
        titleClass : 'pt-3 pb-3 text-subtitle-2',
        titleColor : 'grey',
        infoClass : 'text-h4 pt-0 pb-5',
        infoColor : '#000000',

      }
    }
  },
  data: () => ({

  }),
}
</script>


