import fs from 'fs'
import path from 'path'
import {glob} from 'glob'

const generateFileName = (fname = '') => {
    return fname.split('-').map(word => word.charAt(0).toUpperCase().concat(word.slice(1))).join(' ').replace('.md','')
}

const generateLinkToFile = (fname = '') => {
    return fname
}

const generateMdItem = (fname) => {

    return {
        text: generateFileName(fname),
        link: generateLinkToFile(fname)
    }
}

const readLevel = (srcDir, to) => {

    let itemList = []

    const dirs = fs.readdirSync(`${srcDir}/${to}/`,{
        recursive: true,
        withFileTypes: true,
    })


    dirs.forEach(
        dir => {
            if(dir.isFile() && !dir.name.includes('index')){
                itemList.push(generateMdItem(dir.name))
            }else if(dir.isDirectory()){
                itemList.push({
                    text: generateFileName(dir.name),
                    base: `/${to}/${dir.name}/`,
                    collapsed: false,
                    items: readLevel(`${srcDir}`,`${to}/${dir.name}`)
                })
            }
        }
    )


    return itemList
}


export default async function(srcDir = 'src/pages/'){

    let sidebarConfig = []

    // Gathering first level of sidebar headers
    let rawDirNames = fs.readdirSync(`${srcDir}`, {
        withFileTypes: true,
    })
    .filter(dir => dir.isDirectory())

    .map(dir => dir.name)
    .sort().reverse()


    console.log(rawDirNames)

    for(const index in rawDirNames){
        const dir = rawDirNames[index]
        const dirName = generateFileName(dir)
        sidebarConfig.push(
            {
                text: dirName,
                collapsed: false,
                base: `/${dir}/`,
                items: readLevel(srcDir, dir),
            })
    }
    return sidebarConfig
}
