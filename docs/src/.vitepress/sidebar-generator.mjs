import { glob, Glob } from 'glob'
import path from 'path'


export default async function(src = "src/pages/")  {
    let sideBarConfig = []

    let topics = await glob(`${src}/**`,{
        maxDepth: 3,
        ignore: '**/index.md',
    })

    let mdFiles = await glob(`${src}/**/*.md`, {
        ignore: '**/index.md',
    })


    topics.slice(1).forEach(source =>  {
        const topicText = path.relative(src, source).split('-').map(e => e.slice(0,1).toUpperCase().concat(e.slice(1))).join(' ')
        const collapsed = false
        const items = mdFiles.filter(file => file.includes(path.relative(src, source)))
        .map(th => {
            /**
             * subfoldering can be implemented
             */
            const itemName = th.split('/').at(-1).replace('.md', '').split('-').map(e => e.slice(0,1).toUpperCase().concat(e.slice(1))).join(' ')
            const link = path.relative(source, th)
            return {
                text: itemName,
                link: link
            }
        })
        const sideBaritem = {
            text: topicText,
            collapsed: collapsed,
            base: `/${path.relative(src, source)}/`,
            link: '',
            items: [
                {
                    items:items
                }
            ]
        }

        sideBarConfig.push(sideBaritem)

        // const items =

    })
    return sideBarConfig

}
