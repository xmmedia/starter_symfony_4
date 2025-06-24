const plugin = require('tailwindcss/plugin');
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    mode: 'jit',
    content: [
        './templates/**/*.html.twig',
        './public/js/src/*.{vue,js}',
        // very specific because a broad include of the js/src dir results in detecting classes in the tinymce code
        './public/js/src/{admin,common,public,user}/**/*.{vue,js}',
    ],
    safelist: [
        // vue transition classes: https://vuejs.org/v2/guide/transitions.html#Transition-Classes
        '.md-enter-active',
        '.md-leave-active',
        '.md-enter',
        '.md-leave-active',
    ],
    theme: {
        screens: {
            'xs': '400px',
            ...defaultTheme.screens,
            'print': { 'raw': 'print' },
            'retina': { 'raw': '(-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi)' },
        },
        extend: {
            colors: {
            },
            borderWidth: {
                '10': '10px',
            },
            maxWidth: {
                '1/2': '50%',
                '3/5': '60%',
                '11/12': '91%',
            },
            fontFamily: {
                'headings': [
                    '"Helvetica Neue"',
                    'Arial',
                    // see https://tailwindcss.com/docs/font-family for list
                    ...defaultTheme.fontFamily.sans,
                ],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
        plugin(({ addComponents, theme }) => {
            addComponents({
                /* goes into the `components` layer, so @apply can see it */
                '.transition-default': {
                    transitionProperty:   theme('transitionProperty.all'),
                    transitionDuration:   theme('transitionDuration.300'),
                    transitionTimingFunction: theme('transitionTimingFunction.in-out'),
                },
            });
        }),
    ],
};
