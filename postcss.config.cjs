/** @type {import('postcss-load-config').Config} */
const config = {
    plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
    ],
    sourceMap : 'prev',
};

module.exports = config;
