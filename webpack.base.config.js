'use strict';
const path = require('path');
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
        .enableVersioning(!Encore.isDevServer())

        // empty the outputPath dir before each build
        .cleanupOutputBeforeBuild()

        // don't output the runtime chunk as we only include 1 JS file per page
        .disableSingleRuntimeChunk()

        // will output as build/admin.js and similar
        .addEntry('admin', './public/js/src/admin.js')
        .addEntry('public', './public/js/src/public.js')

        // adds integrity="..." attributes on your script & link tags
        // requires WebpackEncoreBundle 1.4 or higher
        .enableIntegrityHashes(Encore.isProduction())

        // allow sass/scss files to be processed
        .enableSassLoader(function () {}, {
            // tell sass where to find url() paths/files
            resolveUrlLoaderOptions: {
                root: resolve('public'),
            },
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
        }, { runtimeCompilerBuild: true })

        .enableSourceMaps(Encore.isDev() || Encore.isDevServer())

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

        /* eslint-disable no-unused-vars */
        .configureDefinePlugin((options) => {
            const env = require('dotenv').config({ path: '.env.local' });

            if (env.error) {
                throw env.error;
            }
        })
        /* eslint-enable no-unused-vars */
    ;

    if (Encore.isProduction()) {
        Encore
            .addPlugin(new BundleAnalyzerPlugin({
                analyzerMode: 'static',
                openAnalyzer: false,
            }))
        ;
    }

    if (Encore.isDev()) {
        Encore
            .copyFiles({
                from: './node_modules/svgxuse',
                to: '[name].[hash:8].[ext]',
                pattern: /\.js$/,
            })
        ;
    }
};
