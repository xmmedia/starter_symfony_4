const path = require('path');
const glob = require('glob-all');
const PurgecssPlugin = require('purgecss-webpack-plugin');

// @todo-symfony update
const devDomain = 'dev.example.com';

module.exports = {
    runtimeCompiler: true,
    lintOnSave: false,
    publicPath: process.env.DEV_SERVER ? 'https://'+devDomain+'/dev-server/' : 'build',
    outputDir: 'public/build',

    pages: {
        admin: {
            entry: './public/js/src/admin.js',
            template: './public/js/src/admin.html.twig',
            filename: path.join(__dirname, 'templates/admin.html.twig'),
            minify: false,
        },
        public: {
            entry: './public/js/src/public.js',
            template: './public/js/src/public.html.twig',
            filename: path.join(__dirname, 'templates/public.html.twig'),
            minify: false,
        },
    },

    css: {
        // extract breaks HMR (for CSS)
        // extract: true,
        sourceMap: true,
    },

    transpileDependencies: [
        'vue-apollo', // Object.entries()
    ],

    chainWebpack: config => {
        // Vue's default "@" alias is to src/ whereas our source is in public/js/
        config.resolve.alias.set('@', path.join(__dirname, 'public/js/src'));

        // SVGO loader
        config.module
            .rule('svgo')
            .test(/\.svg$/)
            .use('svgo-loader')
            .loader('svgo-loader')
            .options({
                plugins: [
                    // config targeted at icon files, but should work for others
                    { removeUselessDefs: false },
                    { cleanupIDs: false },
                ],
            })
            .end();

        // remove the plugin for prefetch for both entry points
        // @todo dynamic way to find these?
        config.plugins.delete('prefetch-admin');
        config.plugins.delete('prefetch-public');

        // don't copy everything in the public dir into build dir
        config.plugins.delete('copy');

        if (process.env.NODE_ENV === 'production') {
            // Custom PurgeCSS extractor for Tailwind that allows special characters in class names
            class TailwindExtractor {
                static extract(content) {
                    return content.match(/[A-z0-9-:\/]+/g) || [];
                }
            }

            config.plugin('purgecss')
                .use(new PurgecssPlugin({
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
    },

    pwa: {
        // @todo-symfony update
        name: 'XM Symfony Starter',
        themeColor: '#000000',
        msTileColor: '#000000',
        appleMobileWebAppCapable: true,
        workboxOptions: {
            swDest: '../service-worker.js',
            importWorkboxFrom: 'local',
            chunks: ['public', 'admin'],
            // these options encourage the ServiceWorkers to get in there fast
            // and not allow any straggling "old" SWs to hang around
            clientsClaim: true,
            skipWaiting: true,

            // chunk name is not used because these are refreshed
            // every time the service worker script is updated
            importScripts: ['/js/src/service_worker_import.js'],

            runtimeCaching: [
                {
                    urlPattern: /()/,
                    handler: 'networkFirst',
                    options: {
                        cacheName: 'pages',
                    },
                },
                {
                    urlPattern: /\.(?:js|css|svg)$/,
                    handler: 'networkFirst',
                    options: {
                        cacheName: 'static-resources',
                    },
                },
                // page to show if offline
                // {
                //     urlPattern: /(.*)/,
                //     // based on: https://github.com/GoogleChrome/workbox/issues/757#issuecomment-326672407
                //     handler: ({url, event, params}) => {
                //         return fetch(event.request)
                //             .catch((error) => {
                //                 if (event.request.mode === 'navigate') {
                //                     return caches.match('/offline');
                //                 }
                //
                //                 return error;
                //             });
                //     },
                // },
            ],
        },
        // @todo-symfony update
        iconsPaths: {
            favicon32: 'favicon-32x32.png',
            favicon16: null,
            appleTouchIcon: null,
            maskIcon: null,
            msTileImage: null,
        },
    },

    // Dev server requests to the dev server are proxied through Apache
    devServer: {
        public: devDomain,
        // @todo-symfony update port
        port: 9008,
        contentBase: path.join(__dirname, 'public/'),
        // watchContentBase: false,
        headers: { 'Access-Control-Allow-Origin': '*' },
        inline: true,
        writeToDisk: (filePath) => {
            return /(public|admin)\.html\.twig/.test(filePath);
        },
        compress: true,
        clientLogLevel: 'info',
        historyApiFallback: true,
        // allowedHosts: [
        //     devDomain,
        // ],
        watchOptions: {
            aggregateTimeout: 300,
            poll: 1000,
            ignored: /node_modules/,
        },
    },
}
