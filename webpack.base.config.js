'use strict';
const path = require('path');
const Dotenv = require('dotenv-webpack');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

function resolve (dir) {
    return path.join(__dirname, '.', dir);
}

// Base configuration of Encore/Webpack
module.exports = function (Encore) {
    // Manually configure the runtime environment if not already configured yet by the "encore" command.
    // It's useful when you use tools that rely on webpack.config.js file.
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

        // uncomment to get integrity="..." attributes on your script & link tags
        // requires WebpackEncoreBundle 1.4 or higher
        .enableIntegrityHashes(Encore.isProduction())

        // allow sass/scss files to be processed
        .enableSassLoader(function() {}, {
            // see: http://symfony.com/doc/current/frontend/encore/bootstrap.html#importing-bootstrap-sass
            resolveUrlLoader: false,
        })
        .enablePostCssLoader()
        // allow .vue files to be processed
        .enableVueLoader((options) => {
            options.transpileOptions = {
                transforms: {
                    // required to use gql within template tags
                    // (such as with the ApolloQuery component)
                    dangerousTaggedTemplateString: true,
                },
            };
        })

        // this makes compiling CSS very slow
        // I believe it's mainly because of the size of tailwind
        // .enableSourceMaps(!Encore.isProduction())

        .copyFiles({
            from: './node_modules/svgxuse',
            to: '[name].[hash:8].[ext]',
            pattern: /\.js$/,
        })

        .configureBabel(null, {
            includeNodeModules: [
                'vue-apollo', // Object.entries()
                'hibp', // Object.assign()
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

        .addLoader({
            test: /\.(graphql|gql)$/,
            exclude: /node_modules/,
            loader: 'graphql-tag/loader',
        })

        .addAliases({
            '@': resolve('public/js/src'),
            'vue$': 'vue/dist/vue.esm.js',
        })

        .addPlugin(new Dotenv({
            path: './.env.local',
        }))
    ;

    if (Encore.isProduction()) {
        Encore
            .addPlugin(new BundleAnalyzerPlugin({
                analyzerMode: 'static',
                openAnalyzer: false,
            }))
        ;
    }
};
