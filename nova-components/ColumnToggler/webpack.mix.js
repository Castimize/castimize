let mix = require('laravel-mix')
let tailwindcss = require('tailwindcss');
let postcss = require('postcss-import');

require('./nova.mix')
require('mix-tailwindcss')

mix
  .setPublicPath('dist')
  .js('resources/js/tool.js', 'js')
  .vue({ version: 3 })
    .postCss('resources/css/tool.css', 'dist/css/', [postcss(), tailwindcss('tailwind.config.js')])
  .nova('castimize/column-toggler')
