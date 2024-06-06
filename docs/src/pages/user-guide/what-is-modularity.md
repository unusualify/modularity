---
sidebarPos: 1

---

# What is Modularity
[Unusualify/Modularity](https://github.com/unusualify/modularity) is a Laravel and Vuetify.js powered, developer tool that aims to improve developer experience on conducting full stack development process. On Laravel side, Modularity manages your large scale projects using modules, where a module similar to a single Laravel project, having some views, controllers or models. With the abilities of Vuetify.js, Modularity presents various of dynamic, configurable UI components to auto-construct a CRM for your project.

## Developer Experience

Modularity aims to provide a greate Developer Experince when working on full-stack development process with:
- Presenting various custom artisan commands that undergoes file generation
- Generating CRUD pages and forms based on the defined model using ability of [Vuetify.js](https://vuetifyjs.com/en/)
- Simplistic configuration or customization on the crm panel UI through config files
- Simplistic configuration of CRUD forms through config files
  
## Organized Project Structure

Modular approach trying to resolve the complexity with a default Laravel project structure where every business logic coming together in controllers. In modular approach, each business logic is splitted into different parts that communicate with each other.

Every module is similar to a Laravel project, each one has its own model, views, controllers and route files.

## Dynamic & Configurable Panel UI

Powered by [Vue.js](https://vuejs.org/guide/introduction.html){target="_self"} and [Vuetify](https://vuetifyjs.com/){target="_self"}, your application's administration panel is auto-constructed while you developing your Laravel application.

With the abilities of Vuetify.js, Modularity presents various of dynamic, configurable UI components to auto-construct a CRM for your project.

## Used Packages
- [NWidart/Laravel-Modules](https://github.com/nWidart/laravel-modules){target:"_self"} : is a Laravel package created to manage your large Laravel app using modules. A Module is like a Laravel package, it has some views, controllers or models

## For Questions and Issues

## Future Work

## Main Contributers

<script setup>
import { VPTeamMembers } from 'vitepress/theme'
const members = [
    {
      avatar: 'https://avatars.githubusercontent.com/u/47870922',
      name: 'Oguzhan Bukcuoglu',
      title: 'Creator / Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/OoBook' },
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/45737685',
      name: 'Hazarcan Doga Bakan',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://https://github.com/Exarillion' },
      ]
    },
    
    {
      avatar: 'https://avatars.githubusercontent.com/u/80110747',
      name: 'Ilker Ciblak',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/ilkerciblak' },
        { icon: 'twitter', link: 'https://twitter.com/ilker_exe' }
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/37237628',
      name: 'Gunes Bizim',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/gunesbizim' },
      ]
    },

  ]

</script>

<VPTeamMembers size="small" :members="members" />
