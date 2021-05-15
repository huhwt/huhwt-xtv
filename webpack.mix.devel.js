/**
 * Laravel mix - HuH WebTrees MultiTreeView
 *
 * Output:
 * 		- dist
 *      - huhwt-mult-tv
 *          - app
 *          - resources
 *              - css (minified)
 *              - js (minified)
 *              - views
 *              - sass
 *                  *.scss
 *        module.php
 *        LICENSE.md
 *        README.md
 *      - justlight-x.zip
 *
 */

let mix = require('laravel-mix');
let config = require('./webpack.mix.config');
require('laravel-mix-clean');

 //https://github.com/postcss/autoprefixer
const postcss_autoprefixer = require("autoprefixer")();

//https://github.com/jakob101/postcss-inline-rtl
const postcss_rtl = require("postcss-rtl")();

//https://github.com/bezoerb/postcss-image-inliner
const postcss_image_inliner = require("postcss-image-inliner")({
    assetPaths: [config.webtrees_css_dir],
    maxFileSize: 0,
});

//https://github.com/postcss/postcss-custom-properties
//Enable CSS variables in IE
const postcss_custom_properties = require("postcss-custom-properties")();

mix
.setPublicPath('./')
// .alias('build', config.build_dir)
.sass('src/sass/huhwtXT.scss', config.build_dir + '/css/huhwt.min.css')
.options({
    processCssUrls: false,
    postCss: [
        // postcss_autoprefixer,
        postcss_image_inliner,
        postcss_custom_properties,
    ],
});
