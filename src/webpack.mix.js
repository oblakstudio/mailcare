let mix = require('laravel-mix');

mix.options({
  terser: {
    extractComments: false,
  },
});

mix.webpackConfig({
  stats: {
    warnings: false,
  },
});

mix
  .js('resources/js/app.js', 'public/js')
  .vue()
  .sass('resources/sass/app.scss', 'public/css');
