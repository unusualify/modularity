import fs from 'fs'
import path from 'path'

export default function(){
        const dirs = fs.readdirSync(`./src/pages/`, {
            recursive: true,
            withFileTypes: true,

        })


        var sideBarConfig = {};
        var configJson = dirs.filter(dir => dir.isDirectory()).map(
            dir => {

                    const routeName = dir.name.split('-').map(word => word.charAt(0).toUpperCase().concat(word.slice(1))).join(' ')
                    const mdFiles =  fs.readdirSync(`./src/pages/${dir.name}/`).filter( f => f.split('.').includes('md'))

                    sideBarConfig[`/${dir.name}/`] = {
                        text: routeName,
                        collapsed: false,
                        items: [
                            {
                                items: mdFiles.map( mdFile => {
                                    return {
                                        text: mdFile.split('.')[0].split('-').map(w => w.charAt(0).toUpperCase().concat(w.slice(1).toLowerCase())).join(' '),
                                        link: `./${dir.name}/${mdFile.split('.')[0]}`,
                                    }
                                })
                            }
                        ]
                    }
                }
            )

        return sideBarConfig
}
