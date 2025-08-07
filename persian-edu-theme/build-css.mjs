import { readFile, writeFile } from 'node:fs/promises'
import csso from 'csso'

async function minify(inputPath, outputPath){
  const css = await readFile(inputPath, 'utf8')
  const min = csso.minify(css, { restructure: true }).css
  await writeFile(outputPath, min, 'utf8')
  console.log('Minified', inputPath, '->', outputPath)
}

await minify('assets/css/main.css', 'assets/css/main.min.css')
await minify('assets/css/rtl.css', 'assets/css/rtl.min.css')