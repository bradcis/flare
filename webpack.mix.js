const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const tailwindcss             = require('tailwindcss');
const postCssImport           = require("postcss-import");
const postCssNested           = require("postcss-nested");
const postcssCustomProperties = require("postcss-custom-properties");
const autoprefixer            = require("autoprefixer");

mix.webpackConfig({
  stats: {
    hash: true,
    version: true,
    timings: true,
    children: true,
    errors: true,
    errorDetails: true,
    warnings: true,
    chunks: true,
    modules: false,
    reasons: true,
    source: true,
    publicPath: true,
  }
}).js('resources/js/page-components/tabs.js', 'public/js/page-components')
  .js('resources/js/app.js', 'public/js').react().extract()
  .js('resources/js/helpers/kingdom-unit-movement.js', 'public/js').react().extract()
  .js('resources/js/helpers/admin-chat-messages.js', 'public/js').react().extract()
  .js('resources/js/helpers/admin-site-stats-components.js', 'public/js').react().extract()
  .js('resources/js/helpers/character-boons.js', 'public/js').react().extract()
  .js('resources/js/helpers/admin-statistics.js', 'public/js').react().extract()
  .js('resources/js/helpers/character-inventory.js', 'public/js').react().extract()
  .js('resources/js/helpers/character-sheet.js', 'public/js').react().extract()
  .sass('resources/sass/app.scss', 'public/css')
  .postCss('resources/css/tailwind.css', 'public/css', [
    postCssImport(),
    require('tailwindcss/nesting')(require('postcss-nesting')),
    tailwindcss({ config: './tailwind.config.js' }),
    postCssNested(),
    postcssCustomProperties(),
    autoprefixer(),
  ])
  .combine(
    [
      "resources/vendor/theme/assets/js/script.js",
      "resources/vendor/theme/assets/js/extras.js",
      "resources/vendor/theme/assets/js/components/",
    ],
    "public/js/theme-script.js"
  )
  .combine(
    [
      "node_modules/@popperjs/core/dist/umd/popper.min.js",
      "node_modules/tippy.js/dist/tippy.umd.min.js",
    ],
    "public/js/theme-vendor.js"
  )
  .copy(
    [
      "node_modules/chart.js/dist/Chart.min.js",
      "node_modules/sortablejs/Sortable.min.js",
      "node_modules/@glidejs/glide/dist/glide.min.js",
      "node_modules/@ckeditor/ckeditor5-build-classic/build/ckeditor.js",
    ],
    "public/js/"
  )
  .version()
  .sourceMaps();
