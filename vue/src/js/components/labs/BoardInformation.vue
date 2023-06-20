<template>
  <v-sheet class="v-board-information w-50">
    <v-row>
      <v-col cols="12" xl="9" lg="8" v-fit-grid>
        <v-row>
          <v-col cols="12" class="pb-0">
            <div class="text-h8 pt-4 px-6 text-primary font-weight-bold">
              AT A GLANCE
            </div>
          </v-col>
          <v-col v-fit-grid cols="12" xl="7" lg="6" md="8" sm="8">
            <v-list class="v-callout-list">
              <v-list-item v-for="([title, number, color], index) in statuses" :key="index">
                <ue-callout
                  :title="title"
                  :value="number"
                  :color="color"
                >
                </ue-callout>
              </v-list-item>
            </v-list>
          </v-col>
          <v-col v-fit-grid cols="12" xl="5" lg="6" md="4" sm="4">
            <v-list class="v-details-list">
              <v-list-item v-for="([left,right], index) in details" :key="index" class="pl-4">
                  <div class="mt-1 mb-5 pb-6">
                    <span class="font-weight-bold text-primary">
                      {{ left }}
                    </span>
                    <span class="font-weight-bold text-primary">
                      :
                    </span>
                    <span class="text-grey">
                      {{ right }}
                    </span>
                  </div>
                  <!-- <v-row>
                    <v-col>
                    </v-col>
                  </v-row> -->
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" xl="3" lg="4" v-fit-grid>
        <v-sheet class="d-flex flex-lg-column flex-sm-column align-start align-lg-end justify-lg-center pl-10 pl-lg-0 pr-lg-10">
          <!-- <v-btn>Top Up PACKAGE</v-btn> -->
          <!-- <v-btn-secondary>Top Up PACKAGE</v-btn-secondary> -->
          <v-btn-tertiary class="mb-5 mr-lg-0 mr-5">Top Up PACKAGE</v-btn-tertiary>
          <v-btn-tertiary class="mb-5 mr-lg-0 mr-5">Top Up CREDIT</v-btn-tertiary>
          <!-- <v-btn-cta>Top Up PACKAGE</v-btn-cta>
          <v-btn-cta-secondary>Top Up CREDIT</v-btn-cta-secondary> -->
        </v-sheet>
      </v-col>
    </v-row>
  </v-sheet>
</template>

<script>
export default {
  data () {
    return {
      statuses: [
        ['Pending Approval', '999', 'warning'],
        ['Unapproved', '3', 'error'],
        ['Approved', '0', 'info'],
        ['Published', '5', 'success']
      ],
      details: [
        [
          'Account Type',
          'Pre-paid'
        ],
        [
          'Packages',
          '10'
        ],
        [
          'Credits',
          '$210'
        ]
      ]
    }
  }
}
</script>

<style lang="sass" scoped>
  .v-board-information
    .v-list.v-callout-list,.v-list.v-details-list
      padding: 0
      .v-list-item
        padding-top: 0px
        padding-bottom: 0px
        &.v-list-item--one-line
            padding-right: 0
            padding-left: 0
    .v-list.v-callout-list
      border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
      .v-list-item
        border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))
        border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))

</style>
