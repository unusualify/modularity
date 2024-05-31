# What is Modularity
[Unusualify/Modularity](https://github.com/unusualify/modularity) is a Laravel and Vuetify.js powered, developer tool that aims to improve developer experience on conducting full stack development process. On Laravel side, Modularity manages your large scale projects using modules, where a module similar to a single Laravel project, having some views, controllers or models. With the abilities of Vuetify.js, Modularity presents various of dynamic, configurable UI components to auto-construct a CRM for your project.

## Developer Experience

Modularity aims to provide a greate Developer Experince when working on full-stack development process with:
- Presenting various custom artisan commands that undergoes file generation
- Generating CRUD pages and forms based on the defined model using ability of [Vuetify.js](https://vuetifyjs.com/en/)
- Simplistic configuration or customization on the crm panel UI through config files
- Simplistic configuration of CRUD forms through config files
  
## Organised Project Structure

A module in Modularity project is like a Laravel Project, it has some views, controllers or models. 
![projectStructure]

## Dynamic & Configurable Panel UI


## Used Packages


## For Questions and Issues

## Future Work


## Main Contributers



<script setup>
import { VPTeamMembers } from 'vitepress/theme'
const members = [
    {
      avatar: 'https://avatars.githubusercontent.com/u/47870922',
      name: 'Oguzhan BUKCUOGLU',
      title: 'Creator / Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/OoBook' },
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/80110747',
      name: 'Ilker CIBLAK',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/ilkerciblak' },
        { icon: 'twitter', link: 'https://twitter.com/ilker_exe' }
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/37237628',
      name: 'Gunes BIZIM',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/gunesbizim' },
      ]
    },

  ]

</script>

<VPTeamMembers size="small" :members="members" />
