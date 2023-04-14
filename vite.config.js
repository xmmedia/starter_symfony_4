import { defineConfig } from 'vite';
import basicSslPlugin from '@vitejs/plugin-basic-ssl';
import vuePlugin from '@vitejs/plugin-vue';
import graphqlPlugin from '@rollup/plugin-graphql';
import manifestSRIPlugin from 'vite-plugin-manifest-sri';
import symfonyPlugin from 'vite-plugin-symfony';
import liveReload from 'vite-plugin-live-reload';
import path from 'path';
import dns from 'dns'

dns.setDefaultResultOrder('verbatim');

function resolve (dir) {
    return path.join(__dirname, '.', dir);
}

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
        },
    },
    resolve: {
        alias: {
            '@': resolve('public/js/src'),
        },
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
