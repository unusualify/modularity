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


const generatedHeader = (fname) => {

}

const readLevel = (srcDir, to) => {

    let itemList = []

    const dirs = fs.readdirSync(`${srcDir}/${to}/`,{
        recursive: true,
        withFileTypes: true,
    })


    dirs.forEach(
        dir => {
            if(dir.isFile()){
                itemList.push(generateMdItem(dir.name))
                // console.log(generateMdItem(dir.name))
            }else if(dir.isDirectory()){

                itemList.push({
                    text: dir.name,
                    collapsed: false,
                    items: readLevel(`${srcDir}/${to}`,dir.name)
                })
            }
        }
    )

    console.log(itemList)
    return itemList
}






export default async function(srcDir = 'src/pages/'){

    let sidebarConfig = []

    // Gathering first level of sidebar headers
    let rawDirNames = await glob(`${srcDir}/**`, {
        maxDepth: 3,
        ignore: '**/index.md'

    })
    .then(dirs => dirs.map(dir => path.relative(srcDir, dir))
    .filter(dir => dir.length)) // filtered ''(srcDir itself) with string pattern usage

    for(const index in rawDirNames){
        const dir = rawDirNames[index]
        const dirName = generateFileName(dir)
        let dirMdFiles = []


        sidebarConfig.push(
            {
                text: dirName,
                collapsed: false,
                base: `/${dir}/`,
                items: readLevel(srcDir, dir),
            })
    }
    // console.log(sidebarConfig)c
    console.log(sidebarConfig)
    return sidebarConfig
}
