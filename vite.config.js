import { sentryVitePlugin } from "@sentry/vite-plugin";
import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import mkcert from'vite-plugin-mkcert';
import vuePlugin from '@vitejs/plugin-vue';
import graphqlPlugin from '@rollup/plugin-graphql';
import manifestSRIPlugin from 'vite-plugin-manifest-sri';
import symfonyPlugin from 'vite-plugin-symfony';
import dns from 'dns';

dns.setDefaultResultOrder('verbatim');

export default defineConfig({
    plugins: [
        mkcert(),
        vuePlugin(),
        graphqlPlugin(),
        manifestSRIPlugin(),
        symfonyPlugin({
            refresh: true,
            // sriAlgorithm: 'sha384',
        }),
        sentryVitePlugin({
            // @todo-symfony
            org: 'xm-media',
            project: 'symfony-starter',
        }),
    ],
    build: {
        outDir: 'public/build',
        rollupOptions: {
            input: {
                admin: './public/js/src/admin.js',
                user: './public/js/src/user.js',
            },
            output: {
                manualChunks: {
                    'vue-final-modal': ['vue-final-modal'],
                    zxcvbn: ['zxcvbn'],
                }
            }
        },
        sourcemap: true,
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./public/js/src', import.meta.url)),
        },
        // the default plus .vue
        extensions: ['.vue', '.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json'],
    },
    css: {
        devSourcemap: true,
    },
    server: {
        host: true,
        port: 9008,
        origin: 'https://localhost:9008',
        strictPort: true,
        https: true,
        watch: {
            // this is in part needed because the symfony plugin ignores the public dir completely
            ignored: ['**/vendor/**', '**/var/**'],
        },
    },
    appType: 'custom',
    clearScreen: false,
});
