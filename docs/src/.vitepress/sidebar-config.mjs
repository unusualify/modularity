import { defineConfig } from 'vitepress'
import sidebarGenerate from './sidebar-generator-v2.mjs'
export const sidebarConfig = defineConfig({
    sidebar: await sidebarGenerate('./src/pages/')
})
