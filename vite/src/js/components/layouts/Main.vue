<template>
  <v-app id="inspire">

    <ue-sidebar
      :items="sidebarItems"  
      ref="sidebar" 
      
    />

    <v-app-bar app>
      <v-app-bar-nav-icon 
        v-if="$root.showToggleButton"
        @click="$root.toggleSidebar">
      </v-app-bar-nav-icon>

      <v-toolbar-title>CRM</v-toolbar-title>
      
      <v-btn
        v-if="false"
        class="ma-2"
        :loading="loading"
        :disabled="loading"
        color="secondary"
        @click="addSidebarItem"
      >
        Add Sidebar Item
      </v-btn>

      <!-- #language selector -->
      <v-toolbar-title v-if="false">
        <!-- {{ $tc('list') }}
        {{ $n(100.77, 'currency') }} -->
        {{ $t('language-select') }}
        <select v-model="$i18n.locale">
          <option v-for="(lang, i) in langs" :key="`Lang${i}`" :value="lang">
            {{ lang }}
          </option>
        </select>
      </v-toolbar-title>

    </v-app-bar>

    <v-main>
      <!--  -->
      <!-- <ue-footer :items="footerLinks" /> -->
      <div>
          <v-breadcrumbs :items="breadcrumbs">
            <template v-slot:divider>
              <v-icon>mdi-forward</v-icon>
            </template>
          </v-breadcrumbs>
      </div>
      <slot></slot>

      <ue-media
        ref="mediaLibrary"
      ></ue-media>

    </v-main>

  </v-app>
</template>

<script>
  export default {
    props: {
      configuration: {
        type: Object,
        default() {
          return {
            "sideMenu": [
              {
                icon: '$package',
                text: 'Packages',
                action: '',
                is_active: false
              },
              {
                icon: '$creditCards',
                text: 'Payments',
                action: '',
                is_active: false
              },
              {
                icon: '$product',
                text: 'Customers',
                action: '',
                is_active: false
              },
              {
                icon: '$users',
                text: 'Users',
                is_active: true,
                items: [
                  {
                    icon: '$users',
                    text: 'Show Users',
                    action: '',
                    is_active: false
                  },
                  {
                    icon: '$userAdd',
                    text: 'Add User',
                    action: '',
                    is_active: false
                  },
                  {
                    icon: '$role',
                    text: 'Roles',
                    is_active: true,
                    items: [
                      {
                        icon: '$permission',
                        text: 'Add Permission',
                        action: '',
                        is_active: true
                      },
                    ]
                  },
                ]
              },

              {
                icon: '$check',
                text: 'Settings',
                action: '',
                is_active: false
              },
              {
                icon: 'fas fa-plus',
                text: 'Others',
                action: '',
                is_active: false
              },
            ]
          };
        }
      }
    },
    data() {
      return {
        loader: null,
        loading:false,
        langs: ['tr', 'en'],

        footerDisplay: false,
        sidebarItem: [
            {
              icon: '$package',
              text: 'Packages',
              action: ''
            },
            {
              icon: '$creditCards',
              text: 'Payments',
              action: ''
            },
            {
              icon: '$product',
              text: 'Customers',
              action: ''
            },
            {
              icon: '$users',
              text: 'Users',
              items: [
                {
                  icon: '$users',
                  text: 'Show Users',
                  action: ''
                },
                {
                  icon: '$userAdd',
                  text: 'Add User',
                  action: ''
                },
              ]
            },

            {
              icon: '$check',
              text: 'Settings',
              action: ''
            },
            {
              icon: 'fas fa-plus',
              text: 'Others',
              action: ''
            },
        ],

        sidebarItems: this.configuration.sideMenu,
        // activeItem: this.configuration.activeItem ?? 0,
        // activeSubItem: this.configuration.activeSubItem ?? -1,
        breadcrumbs: this.configuration.breadcrumbs ?? [],

        footerLinks: [
          {
            icon: 'mdi-facebook',
            url:  'facebook.com'
          },
          {
            icon: 'mdi-twitter',
            url:  'twitter.com'
          },
          {
            icon: 'mdi-linkedin',
            url:  'linkedin.com'
          },
          {
            icon: 'mdi-instagram',
            url:  'instagram.com'
          }
        ]
      }
    },

    created() {
      // this.$vToastify.success("Kaydetme İşleminiz Başarılı!", 'Başarılı');
    
      // this.$toast.success('Info toast')
      
      // this.$notification.success("hello world", {  timer: 5 });

      // console.log( this.$vuetify.icons );

      // console.log('mounted items', this.$refs)
    },

    mounted() {

    },

    methods: {
      addSidebarItem: function(event){
        // drawer = !drawer
        // this.loading = true;
        // const l = this.loader;
        // this[l] = !this[l];
        
        // this.sidebarItem.push( {
        //   icon: "fas fa-circle-question",
        //   text: "New"
        // });

        this.$refs.sidebar.addItem( {icon: "fa-regular fa-circle-question", text: "New"} );

        // setTimeout(() => (this[l] = false), 3000)
        
        // this.loader = null;
      },
      
      submit () {
        alert('Submit Form')
      }

      
    }
  }
</script>