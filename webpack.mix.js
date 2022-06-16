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

mix.webpackConfig({
    devServer: {
        server: 'https',
    },
    stats: {
        hash: true,
        children: true,
        errors: true,
        errorDetails: true,
        warnings: true,
        publicPath: true,
    }
});

mix.sourceMaps().ts('resources/js/app.ts', 'public/js').vue({
    extractVueStyles: false,
    // options: {
    //     loaders: {
    //         scss: 'vue-style-loader!css-loader!sass-loader',
    //         sass: 'vue-style-loader!css-loader!sass-loader?indentedSyntax',
    //     },
    // },
});

mix.sass('resources/sass/app.scss', 'public/css')
    // .styles([], 'public/css/all.css')
    .sourceMaps();

mix.version();

