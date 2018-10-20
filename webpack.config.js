'use strict';
// This is for dev, watch and build (production)
const Encore = require('@symfony/webpack-encore');
const encoreConfigure = require('./webpack.base.config');
const merge = require('webpack-merge');

encoreConfigure(Encore);

let config = merge(Encore.getWebpackConfig(), require('./webpack.customize'));

if (Encore.isProduction()) {
    config.devtool = 'source-map';
}

module.exports = config;