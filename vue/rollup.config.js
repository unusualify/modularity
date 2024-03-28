


export default {
  // make sure to externalize deps that shouldn't be bundled
  // into your library
  // external: [/node_modules/,'vue',],

  input: {
    // 'core-index': resolve(__dirname + "/src/js", 'core-index.js'),
    'core-auth': resolve(__dirname, 'src/js/core-auth.js'),
    'core-dashboard': resolve(__dirname, 'src/js/core-dashboard.js'),
    'core-form': resolve(__dirname, 'src/js/core-form.js'),
    'core-free': resolve(__dirname, 'src/js/core-free.js'),
    'core-index': resolve(__dirname, 'src/js/core-index.js'),
  },
  // input: Object.fromEntries(
  //   globSync('src/js/core-*.js').map(file => [
  //     // This remove `src/` as well as the file extension from each
  //     // file, so e.g. src/nested/foo.js becomes nested/foo
  //     path.relative(
  //       'src',
  //       file.slice(0, file.length - path.extname(file).length)
  //     ),
  //     // This expands the relative paths to absolute paths, so e.g.
  //     // src/nested/foo becomes /project/src/nested/foo.js
  //     fileURLToPath(new URL(file, import.meta.url))
  //   ])
  // ),

  output: {
      // entryFileNames: `[name].js`,
      // // chunkFileNames: `[name].[hash].js`,
      assetFileNames: (assetInfo) => {
        // console.log(assetInfo.name)
        let extType = assetInfo.name.split('.').at(1);
        if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
          extType = 'img';
        }

        if (/eot|woff2?|ttf/i.test(extType)) {
          extType = 'fonts';
        }

        // return `modularity/${extType}/[name].asset[extname]`;
        return `${extType}/[name].[hash][extname]`;
      },

      chunkFileNames: 'js/[name].[hash].js',
      entryFileNames: 'entries/[name].js',

      // preserveModules: false,
      // globals: {
      //     vue: 'Vue',
      // },
  },
  // output: {
  //   // assetFileNames: '[name].[hash][extname]'
  //   assetFileNames: (assetInfo) => {
  //     // console.log(assetInfo.name)
  //     let extType = assetInfo.name.split('.').at(1);
  //     if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
  //       extType = 'img';
  //     }

  //     // return `modularity/${extType}/[name].asset[extname]`;
  //     return `modularity/${extType}/[name].[hash][extname]`;
  //   },

  //   // chunkFileNames: 'modularity/js/[name].chunk.js',
  //   chunkFileNames: 'modularity/js/[name].[hash].js',

  //   // entryFileNames: 'modularity/[extname]/[name].[extname]',
  //   entryFileNames: (chunkInfo) => {
  //     // console.log(chunkInfo)

  //     // return '[name].entry.js'
  //     return 'modularity/js/[name].[hash].js'
  //   }


  // },
};
