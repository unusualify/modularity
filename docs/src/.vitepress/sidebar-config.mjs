import { defineConfig } from 'vitepress'
import  userSidebarConfigs   from '../pages/user-guide/sidebar-generator.mjs'

export const sidebarConfig = defineConfig({


    sidebar: {
        '/user-guide/': {
            base: '/user-guide/', items: userSidebarConfigs()
        },
        '/developer-guide/' : {
            base: '/developer-guide/', items : []
        }


    },
})







