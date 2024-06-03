import { defineConfig } from 'vitepress'

import sideBarV2 from './sidebar-generator.mjs'
// sideBarV2('./src/pages')
export const sidebarConfig = defineConfig({
    sidebar: await sideBarV2('./src/pages')
})







