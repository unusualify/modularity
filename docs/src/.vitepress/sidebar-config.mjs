import { defineConfig } from 'vitepress'


export const sidebarConfig = defineConfig({
    sidebar: {
        '/lib/user-guide/' : [
            {
              text: 'Examples',
              collapsed: true,
              items: [
                {
                    items : [
                    { text: 'Runtime API Examples', link: 'lib/developer-guide/api-examples' },
                    { text: 'Markdown Examples', link: 'lib/user-guide/markdown-examples' }

                    ]
            }
              ]
            }
          ],
    },
})


function userGuide(){

}
