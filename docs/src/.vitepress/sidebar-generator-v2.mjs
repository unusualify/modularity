import fs from 'fs'
import  matter  from 'gray-matter'

const readFrontMatterSync = (fname) => {
  try {
    const readFile = fs.readFileSync(`${fname}`, 'utf-8')
    const data = matter(readFile).data
    return {
      sidebarPos: data?.sidebarPos ?? 99,
      text: data?.sidebarTitle ?? '',
    }
  } catch (error) {
    return {
      sidebarPos: 99,
    }
  }
}

const generateFileName = (fname = '') => {
  return fname.split('-').map(word => word.charAt(0).toUpperCase().concat(word.slice(1))).join(' ').replace('.md','')
}

const generateLinkToFile = (fname = '') => {
  return fname
}

const generateMdItem = (fname, sidebarPos) => {
  return {
    text: generateFileName(fname),
    link: generateLinkToFile(fname),
    sidebarPos: sidebarPos,
  }
}

const readLevel =  (srcDir, to) => {
  let itemList = []

  const dirs = fs.readdirSync(`${srcDir}/${to}/`,{
    recursive: true,
    withFileTypes: true,
  })

  dirs.forEach(
    dir =>  {
      if(dir.isFile() && !dir.name.includes('index')){

        const filematter = readFrontMatterSync(`${srcDir}/${to}/${dir.name}`)
        itemList.push(generateMdItem(dir.name, filematter.sidebarPos))
        }else if(dir.isDirectory()){
          itemList.push({
            text: generateFileName(dir.name),
            base: `/${to}/${dir.name}/`,
            collapsed: true,
            sidebarPos: readFrontMatterSync(`${srcDir}/${to}/${dir.name}/index.md`)?.sidebarPos,
            items: readLevel(`${srcDir}`,`${to}/${dir.name}`),
          })
        }
    }
  )
  itemList.sort((a,b) => a.sidebarPos - b.sidebarPos)

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
