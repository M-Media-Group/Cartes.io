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

mix.sourceMaps().js('resources/js/app.js', 'public/js').extract();
mix.sass('resources/sass/app.scss', 'public/css').styles([

], 'public/css/all.css').sourceMaps().version();

mix.options({
  extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//  processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
	purifyCss: {
	    purifyOptions: {
	        whitelist: ['*leaflet*', 'badge', 'code']
	    },
	}
//  uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//  postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
});