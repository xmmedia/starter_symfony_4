'use strict';
// This is for dev, watch and build (production)
const Encore = require('@symfony/webpack-encore');
const encoreConfigure = require('./webpack.base.config');
const WorkboxPlugin = require('workbox-webpack-plugin');
const merge = require('webpack-merge');
const path = require('path');

process.env.NODE_ENV = process.env.NODE_ENV || (Encore.isProduction() ? 'production' : 'development');

encoreConfigure(Encore);

if (!Encore.isDevServer()) {
    Encore.addPlugin(new WorkboxPlugin.InjectManifest({
        swSrc: path.join(__dirname, 'public/js/src/service_worker.js'),

        // don't put this in a sub folder as the sw will be just for the folder
        // though this could be useful if you want more than 1 service worker
        swDest: path.join(__dirname, 'public/service-worker.js'),
        importWorkboxFrom: 'local',
        // @todo-symfony customize these lists depending on if the sw is for admins only or for public and admin; these are used to determine what's put in the precache manifest; by default, NOTHING IS PRECACHED; see: https://developers.google.com/web/tools/workbox/modules/workbox-webpack-plugin#full_injectmanifest_config
        // by default, we only cache the icon files, favicon, and manifest
        chunks: [''],
        // excludeChunks: [],
        maximumFileSizeToCacheInBytes: 20 * 1024 * 1024, // 20mb

        globDirectory: 'public/',
        globPatterns: [
            'favicon.ico',
            'site.webmanifest',
        ],

        // list of files that are generated outside of webpack
        // the array will be used to generate a revision for the file
        // that will be included as part of the precache manifest
        // templatedURLs: {
        //     '/admin': [
        //         'js/src/**/*.js',
        //         'js/src/**/*.vue',
        //         'css/**/*.scss',
        //     ],
        // },
    }));
}

let config = merge(Encore.getWebpackConfig(), require('./webpack.customize'));

module.exports = config;
