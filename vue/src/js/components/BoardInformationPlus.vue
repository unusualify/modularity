<template>
  <!-- TODO: V2: This component and it's incoming config can be changed and can be made with RecursiveStuff -->
  <v-card :color="container.color" density="comfortable" :elevation="container.elevation" :class="container.class">
   <!-- {{ console.log('here2') }} -->

    <v-card-title style="font-weight: bolder" class="py-4"> {{ $t("AT A GLANCE") }}</v-card-title>

    <v-card-item class="pt-0 pb-4">
      <v-row class="" densex>
        <v-col
        v-for="(card, i) in cards"
        :key="i"
        :cols="card.flex"
        class=""
        >

          <v-card
            :variant="cardAttribute.variant"

            :border="cardAttribute.border"
            :class="cardAttribute.class"
            :style="{
              'border-radius': cardAttribute.borderRadius,
              'border-color': `${cardAttribute.borderColor} !important`,
            }">

            <div class="d-flex">

              <div class="d-flex flex-column justify-center align-center pr-3">
                <div class="glance-icon-container"
                  :style="`background-color: ${card.iconBackground};`">
                  <v-icon
                    :icon="card.icon"
                    :color="card.iconColor"
                    :size="card.iconSize"
                  >
                  </v-icon>
                </div>
              </div>

              <div class="d-flex flex-column">
                <v-card-item
                  align="left"
                  :class="cardAttribute.infoClass"
                  :style="{
                    'line-height': cardAttribute.infoLineHeight,
                    'font-weight': cardAttribute.infoFontWeight,
                    'color':  card.infoColor,
                  }"
                >
                  {{ card.data.items }}
                </v-card-item>
                <v-card-title
                  align="center"
                  :class="cardAttribute.titleClass"
                  :style="
                  {
                    'color' : cardAttribute.titleColor,
                    'line-height': cardAttribute.infoLineHeight
                  }"
                >
                  {{ $t(card.title) }}
                </v-card-title>

              </div>

            </div>

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
        class : 'px-6 py-5'
      }
    },
    cardAttribute: {
      type: Object,
      default: {
        variant : 'outlined',
        borderRadius : 14,
        class: 'px-5',
        border : 'sm',
        titleClass : 'pt-3 pb-3 text-subtitle-2',
        titleColor : 'grey',
        infoClass : 'text-h4 pa-0',
        infoColor : '#000000',
        infoLineHeight: '1',
        infoFontWeight: '700',

      }
    }
  },
  data: () => ({

  }),
}
</script>


<style lang="scss">
.glance-icon-container{
  width: 56px;
  height: 56px;
  align-items: center;
  justify-content: center;
  display: flex;
  border-radius: 50%;
}
</style>
