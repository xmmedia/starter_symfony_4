import { sentryVitePlugin } from "@sentry/vite-plugin";
import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import mkcert from 'vite-plugin-mkcert';
import vuePlugin from '@vitejs/plugin-vue';
import graphqlPlugin from '@rollup/plugin-graphql';
import manifestSRIPlugin from 'vite-plugin-manifest-sri';
import symfonyPlugin from 'vite-plugin-symfony';
import tailwindcss from '@tailwindcss/vite';
import dns from 'dns';
import { existsSync, readFileSync } from 'node:fs';

dns.setDefaultResultOrder('verbatim');

// in the Lando node service use the cert Lando issues it; mkcert can't run there
// (needs root, & a CA made in the container isn't trusted by the host browser)
const certPath = '/certs/cert.crt';
const keyPath = '/certs/cert.key';
const inLando = existsSync(certPath) && existsSync(keyPath);
// @todo-symfony update to match your local dev URL
const siteOrigin = 'https://symfonystarter.lndo.site';
const https = inLando ? {
    cert: readFileSync(certPath),
    key: readFileSync(keyPath),
} : true;

export default defineConfig(({ command, isPreview }) => {
    return {
        plugins: [
            ...(inLando ? [] : [mkcert()]),
            vuePlugin(),
            graphqlPlugin(),
            manifestSRIPlugin(),
            symfonyPlugin({
                refresh: true,
                sriAlgorithm: 'sha384',
            }),
            sentryVitePlugin({
                disable: process.env.NODE_ENV !== 'production' && !!process.env.SENTRY_AUTH_TOKEN,
                authToken: process.env.SENTRY_AUTH_TOKEN,
                // @todo-symfony
                org: 'xm-media',
                project: 'symfony-starter',
                telemetry: process.env.NODE_ENV === 'production',
            }),
            tailwindcss(),
        ],
        // the dev base must match the proxied path in lando_apache_vite.conf;
        // preview runs as `serve`, but should mirror the production paths
        base: 'build' === command || isPreview ? '/build/' : '/vite-dev/',
        build: {
            outDir: 'public/build',
            rolldownOptions: {
                input: {
                    admin: './public/js/src/admin.js',
                    user: './public/js/src/user.js',
                },
            },
            sourcemap: 'serve' === command,
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
            // @todo-symfony change port number 2x (must match lando_apache_vite.conf)
            port: 9008,
            // always reached through the appserver proxy, in the container or on the host
            origin: siteOrigin,
            // the proxy passes the site's Host through; Vite 400s unknown hosts
            allowedHosts: [new URL(siteOrigin).hostname],
            // the HMR socket rides the proxied path, not the Vite port
            hmr: {
                protocol: 'wss',
                host: new URL(siteOrigin).hostname,
                clientPort: 443,
            },
            strictPort: true,
            https,
            watch: {
                // this is in part needed because the symfony plugin ignores the public dir completely
                // matched against the absolute path, so they must start with **/
                // node_modules, .git, the cache dir & build.outDir are already ignored by vite
                ignored: [
                    '**/.idea/**',
                    '**/bin/**',
                    '**/coverage/**',
                    '**/docs/**',
                    '**/migrations/**',
                    // absolute so only the root tests dir is ignored, not e.g. public/js/tests
                    `${fileURLToPath(new URL('./tests/', import.meta.url))}**`,
                    '**/var/**',
                    '**/vendor/**',
                ],
            },
        },
        preview: {
            // @todo-symfony change port number (must match .lando.yml)
            port: 9508,
            strictPort: true,
        },
        appType: 'custom',
        clearScreen: false,
    };
});
