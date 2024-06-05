import { defineConfig } from 'vitepress'
import { shared } from './shared.mjs'
import { navConfig } from './nav-config.mjs'
import { sidebarConfig } from './sidebar-config.mjs'


// https://vitepress.dev/reference/site-config
export default defineConfig({
  ...shared,

  description: "Modularity Docs",
  themeConfig: {
    ...navConfig,
    ...sidebarConfig,
    socialLinks: [
      { icon: 'github', link: 'https://github.com/unusualify/modularity' },
    ],

  },
  // rewrites: {
  //   ':smt/:pkg/(.*)' : ':smt/(.*)'
  // }
})
