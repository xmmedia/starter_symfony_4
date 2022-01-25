'use strict';
// This is for dev, watch and build (production)
const Encore = require('@symfony/webpack-encore');
const encoreConfigure = require('./webpack.base.config');
const merge = require('webpack-merge');

process.env.NODE_ENV = process.env.NODE_ENV || (Encore.isProduction() ? 'production' : 'development');

encoreConfigure(Encore);

let config = merge(Encore.getWebpackConfig(), require('./webpack.customize'));

module.exports = config;
