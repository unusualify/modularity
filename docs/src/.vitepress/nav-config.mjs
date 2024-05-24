import { defineConfig } from "vitepress";

export const navConfig = defineConfig({
    nav: [
        { text: 'Home', link: '/' },
        { text: 'Get Started', link: 'lib/user-guide/get-started' },
        {
          text : 'Version' ,
          items : [
              {text: '1.0.0', link: ''}
          ]}
      ],
})
