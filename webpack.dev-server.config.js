'use strict';
// This is the webpack config used for running the webpack dev-server
// Requests to the dev server are proxied through Apache

const Encore = require('@symfony/webpack-encore');
const encoreConfigure = require('./webpack.base.config');
const merge = require('webpack-merge');

encoreConfigure(Encore);

// Tweak the default webpack config a bit for the dev-server
Encore
    // @todo-symfony
    .setPublicPath('https://symfonystarter.dev2.xmmedia.com/dev-server')
    .setManifestKeyPrefix('build/')
    .enableVersioning(false);

module.exports = merge(Encore.getWebpackConfig(), require('./webpack.customize'));
