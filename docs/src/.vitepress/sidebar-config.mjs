import { defineConfig } from 'vitepress'
import  sideBarGenerate   from '../pages/user-guide/sidebar-generator.mjs'

export const sidebarConfig = defineConfig({


    sidebar: {
        '/user-guide/': {
            base: '/user-guide/', items: sideBarGenerate('user-guide')
        },
        '/developer-guide/' : {
            base: '/developer-guide/', items : sideBarGenerate('developer-guide')
        }


    },
})







