import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import basicSslPlugin from '@vitejs/plugin-basic-ssl';
import vuePlugin from '@vitejs/plugin-vue';
import graphqlPlugin from '@rollup/plugin-graphql';
import manifestSRIPlugin from 'vite-plugin-manifest-sri';
import symfonyPlugin from 'vite-plugin-symfony';
import liveReload from 'vite-plugin-live-reload';
import dns from 'dns'

dns.setDefaultResultOrder('verbatim');

export default defineConfig({
    root: 'public',
    plugins: [
        basicSslPlugin(),
        vuePlugin(),
        graphqlPlugin(),
        manifestSRIPlugin(),
        symfonyPlugin(),
        liveReload([
            // edit live reload paths according to your source code
            __dirname + '/(templates)/**/*.php',
        ]),
    ],
    build: {
        outDir: 'build',
        rollupOptions: {
            input: {
                admin: './public/js/src/admin.js',
                public: './public/js/src/public.js',
            },
            output: {
                manualChunks: {
                    // because it includes a large word list
                    zxcvbn: ['zxcvbn'],
                },
            },
        },
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
        // @todo-symfony set these to a unique port for each project
        port: 9008,
        origin: 'https://localhost:9008',
        strictPort: true,
    },
});
