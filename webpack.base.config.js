'use strict';
const path = require('path');
const glob = require('glob-all');
const PurgecssPlugin = require('purgecss-webpack-plugin');
const Dotenv = require('dotenv-webpack');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

function resolve (dir) {
    return path.join(__dirname, '.', dir);
}

// Base configuration of Encore/Webpack
module.exports = function (Encore) {
    if (!Encore.isRuntimeEnvironmentConfigured()) {
        Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
    }

    Encore
    // directory where all compiled assets will be stored
        .setOutputPath('public/build/')

        // what's the public path to this directory (relative to your project's document root dir)
        .setPublicPath('/build')

        // always create hashed filenames (e.g. public.a1b2c3.css)
        .enableVersioning(true)

        // empty the outputPath dir before each build
        .cleanupOutputBeforeBuild()

        // don't output the runtime chunk as we only include 1 JS file per page
        .disableSingleRuntimeChunk()

        // will output as build/admin.js and similar
        .addEntry('admin', './public/js/src/admin.js')
        .addEntry('public', './public/js/src/public.js')

        // allow sass/scss files to be processed
        .enableSassLoader(function(sassOptions) {}, {
            // see: http://symfony.com/doc/current/frontend/encore/bootstrap.html#importing-bootstrap-sass
            resolveUrlLoader: false,
        })
        .enablePostCssLoader()
        // allow .vue files to be processed
        .enableVueLoader()

        .enableSourceMaps(true)

        .configureBabel(() => {}, {
            includeNodeModules: [
                'vue-apollo', // Object.entries()
            ],
        })

        .addLoader({
            test: /\.svg$/,
            use: [
                {
                    loader: 'svgo-loader',
                    options: {
                        plugins: [
                            // config targeted at icon files, but should work for others
                            { removeUselessDefs: false },
                            { cleanupIDs: false },
                        ],
                    },
                },
            ],
        })

        .addAliases({
            '@': resolve('public/js/src'),
            'vue$': 'vue/dist/vue.esm.js',
        })

        .addPlugin(new Dotenv())

        .addPlugin(new BundleAnalyzerPlugin({
            analyzerMode: 'static',
            openAnalyzer: false,
        }))
    ;

    if (Encore.isProduction()) {
        // Custom PurgeCSS extractor for Tailwind that allows special characters in class names
        class TailwindExtractor {
            static extract(content) {
                return content.match(/[A-Za-z0-9-_:\/]+/g) || [];
            }
        }

        Encore
            .addPlugin(new PurgecssPlugin({
                // Specify the locations of any files you want to scan for class names.
                paths: glob.sync([
                    path.join(__dirname, 'templates/**/*.html.twig'),
                    path.join(__dirname, 'public/js/src/**/*.vue'),
                    path.join(__dirname, 'public/js/src/**/*.js'),
                    path.join(__dirname, 'node_modules/vue-js-modal/dist/index.js'),
                ]),
                extractors: [
                    {
                        extractor: TailwindExtractor,
                        // Specify the file extensions to include when scanning for class names
                        extensions: ['html', 'js', 'php', 'vue', 'twig'],
                    },
                ],
                whitelistPatterns: [
                    // vue transition classes: https://vuejs.org/v2/guide/transitions.html#Transition-Classes
                    /-enter/,
                    /-leave/,
                ],
            }));
    }
};
