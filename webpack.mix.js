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

// Set public path and configure for production
mix.setPublicPath('public')
  .options({
    processCssUrls: false,
    postCss: [
      require('autoprefixer'),
      require('cssnano')({
        preset: 'default',
      }),
    ],
  });

// Frontend Assets (for user, guest, and public pages)
mix.js('resources/js/front/app.js', 'public/assets/front/js/app.js')
  .sass('resources/sass/front/app.scss', 'public/assets/front/css/app.css');

// Admin Assets
mix.js('resources/js/admin/app.js', 'public/assets/admin/js/app.js')
  .sass('resources/sass/admin/app.scss', 'public/assets/admin/css/app.css');

// Production optimizations
if (mix.inProduction()) {
  // Version files for cache busting in production
  mix.version();

  // Additional production optimizations
  mix.options({
    terser: {
      terserOptions: {
        compress: {
          drop_console: true, // Remove console.log in production
        },
      },
    },
  });
} else {
  // Development optimizations
  mix.sourceMaps();
}
