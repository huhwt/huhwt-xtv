/**
 * Laravel mix - HuH WebTrees MultiTreeView
 *
 * Output:
 * 		- dist
 *      - huhwt-xtv
 *          - app
 *              - Http
 *                  - RequestHandlers
 *              - Module
 *                  - InteractiveTree
 *              - Services
 *          - resources
 *              - css (minified)
 *              - js (minified)
 *              - views
 *              - lang
 *                  - de
 *        autoload.php
 *        module.php
 *        LICENSE.md
 *        README.md
 *        README.de.md
 *
 */

let mix = require('laravel-mix');
let config = require('./webpack.mix.config');
require('laravel-mix-clean');

const version  = '1.0.1';
const dist_dir = 'dist/huhwt-xtv';
const dist_root = 'dist';

//https://github.com/gregnb/filemanager-webpack-plugin
const FileManagerPlugin = require('filemanager-webpack-plugin');

mix
    .setPublicPath('./dist')
    .copy(config.build_dir + '/css/huhwt.min.css', dist_dir + '/resources/css/huhwt.min.css')
    .copyDirectory(config.public_dir + '/views', dist_dir + '/resources/views')
    .copy(config.public_dir + '/*.php', dist_dir + '/resources')
    .copyDirectory(config.app_dir, dist_dir)
    .copy(config.dev_dir + '/js/huhwt-treeviewXT.js', dist_dir + '/resources/js/huhwtXT.min.js')
    .copy(config.dev_dir + '/js/html2canvas.js', dist_dir + '/resources/js/html2canvas.js')
    .copy(config.dev_dir + '/lang/de/messages.po', dist_dir + '/resources/lang/de/messages.po')
    .copy('autoload.php', dist_dir)
    .copy('module.php', dist_dir)
    .copy('InteractiveTreeXT.php', dist_dir)
    .copy('InteractiveTreeXTmod.php', dist_dir)
    .copy('latest-version.txt', dist_dir)
    .copy('LICENSE.md', dist_dir)
    .copy('README.md', dist_dir)
    .copy('README.de.md', dist_dir)
    .copy('latest-version.txt', dist_dir)
    .webpackConfig({
        plugins: [
          new FileManagerPlugin({
            onEnd: {
                archive: [
                    { source: './dist', destination: './dist/huhwt-xtv-' + version + '.zip'}
                  ]
            }
          })
        ]
    })
    .clean();
