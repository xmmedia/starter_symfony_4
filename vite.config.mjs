import { sentryVitePlugin } from "@sentry/vite-plugin";
import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import vuePlugin from '@vitejs/plugin-vue';
import graphqlPlugin from '@rollup/plugin-graphql';
import manifestSRIPlugin from 'vite-plugin-manifest-sri';
import symfonyPlugin from 'vite-plugin-symfony';
import tailwindcss from '@tailwindcss/vite';
import dns from 'dns';

dns.setDefaultResultOrder('verbatim');

// runs in the DDEV web container, behind https it doesn't serve itself: the dev server through
// Apache's proxy at /vite-dev/ (.ddev/web-build/apache-vite.conf), so dev assets are same-origin
// with the site (browsers won't load an SVG <use> from another origin), & the preview through
// DDEV's router (web_extra_exposed_ports in .ddev/config.yaml). Both use Vite's default ports,
// set explicitly as apache-vite.conf & .ddev/config.yaml rely on them: they're per container &
// the router routes by hostname, so every project uses the same ones
// includes the router's port, when it isn't 443; only the dev server uses it
const siteUrl = new URL(process.env.DDEV_PRIMARY_URL || 'https://localhost');

export default defineConfig(({ command, mode, isPreview }) => {
    // envs that don't use Sentry have no token, so nothing's uploaded (or generated) there
    const uploadSourceMaps = !!process.env.SENTRY_AUTH_TOKEN && 'build' === command && 'production' === mode;

    // Vitest loads this config as `serve` too, & doesn't need DDEV
    if ('serve' === command && !isPreview && !process.env.VITEST && !process.env.DDEV_PRIMARY_URL) {
        throw new Error('The dev server only runs in DDEV, behind its Apache proxy: use `ddev yarn dev`');
    }

    return {
        plugins: [
            vuePlugin({
                // leave absolute URLs (/images/…) in templates as-is: they're served from
                // the webroot, not resolved as imports (which fail without a publicDir;
                // vite-plugin-symfony disables it)
                template: { transformAssetUrls: { includeAbsolute: false } },
            }),
            graphqlPlugin(),
            manifestSRIPlugin(),
            symfonyPlugin({
                refresh: true,
                sriAlgorithm: 'sha384',
            }),
            sentryVitePlugin({
                disable: !uploadSourceMaps,
                authToken: process.env.SENTRY_AUTH_TOKEN,
                // @todo-symfony
                org: 'xm-media',
                project: 'symfony-starter',
                telemetry: false,
                // a Sentry outage shouldn't block a deploy: warn & carry on without the upload
                // eslint-disable-next-line no-console
                errorHandler: (err) => console.warn(err),
                // the maps are only for Sentry: don't deploy them
                sourcemaps: { filesToDeleteAfterUpload: ['public/build/**/*.map'] },
            }),
            tailwindcss(),
        ],
        // the dev base must match the proxied path in apache-vite.conf; preview runs as `serve`,
        // but should mirror the production paths
        base: 'build' === command || isPreview ? '/build/' : '/vite-dev/',
        build: {
            outDir: 'public/build',
            rolldownOptions: {
                input: {
                    admin: './public/js/src/admin.js',
                    user: './public/js/src/user.js',
                },
            },
            // hidden: generated for Sentry, but not referenced from the bundles
            sourcemap: uploadSourceMaps ? 'hidden' : ('serve' === command),
            // don't inline assets
            assetsInlineLimit: 0,
        },
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./public/js/src', import.meta.url)),
            },
        },
        css: {
            devSourcemap: true,
        },
        server: {
            host: true,
            // Vite's default
            port: 5173,
            strictPort: true,
            origin: siteUrl.origin,
            // the proxy passes the site's Host through; Vite 400s unknown hosts
            allowedHosts: [siteUrl.hostname],
            // the HMR socket rides the proxied path, not the Vite port
            hmr: {
                protocol: 'wss',
                host: siteUrl.hostname,
                clientPort: Number(siteUrl.port || 443),
            },
            watch: {
                // this is in part needed because the symfony plugin ignores the public dir completely
                // matched against the absolute path, so they must start with **/
                // node_modules, .git, vite's cacheDir & build.outDir are already ignored by vite
                ignored: [
                    '**/.ddev/**',
                    '**/.idea/**',
                    '**/bin/**',
                    // Symfony's cache (Kernel::getCacheDir()), eg the profiler writing on every request
                    `${fileURLToPath(new URL('./cache/', import.meta.url))}**`,
                    '**/coverage/**',
                    '**/docs/**',
                    // absolute so only the root tests dir is ignored, not e.g. public/js/tests
                    `${fileURLToPath(new URL('./tests/', import.meta.url))}**`,
                    // absolute: '**/var/**' also matches the project's own path in DDEV (/var/www/html),
                    // so it ignores every file & hot reload never fires
                    `${fileURLToPath(new URL('./var/', import.meta.url))}**`,
                    '**/vendor/**',
                ],
            },
        },
        preview: {
            // Vite's default
            port: 4173,
            strictPort: true,
        },
        appType: 'custom',
        clearScreen: false,
    };
});
