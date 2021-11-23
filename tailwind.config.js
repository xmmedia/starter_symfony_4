const plugin = require('tailwindcss/plugin');
const colors = require('tailwindcss/colors');

module.exports = {
    mode: 'jit',
    purge: {
        content: [
            './templates/**/*.html.twig',
            './public/js/src/**/*.vue',
            './public/js/src/**/*.js',
        ],
        options: {
            safelist: [
                // vue transition classes: https://vuejs.org/v2/guide/transitions.html#Transition-Classes
                '.md-enter-active',
                '.md-leave-active',
                '.md-enter',
                '.md-leave-active',
            ],
        },
    },
    theme: {
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            inherit: 'inherit',

            black: colors.black,
            white: colors.white,
            gray: colors.trueGray,
            orange: colors.orange,
            green: colors.green,
            // teal: colors.teal,
            blue: colors.blue,
            yellow: colors.yellow,
            // indigo: colors.indigo,
            red: colors.red,
            // pink: colors.pink,
            // purple: colors.purple,
        },
        extend: {
            borderWidth: {
                '10': '10px',
            },
            maxWidth: {
                '1/2': '50%',
                '3/5': '60%',
                '11/12': '91%',
            },
            screens: {
                'xs': '400px',
                '2xl': '1536px',
                'print': { 'raw': 'print' },
                'retina': { 'raw': '(-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi)' },
            },
            fontFamily: {
                'headings': [
                    '"Helvetica Neue"',
                    'Arial',
                    'sans-serif',
                    '"Apple Color Emoji"',
                    '"Segoe UI Emoji"',
                    '"Segoe UI Symbol"',
                    '"Noto Color Emoji"',
                ],
            },
        },
    },
    variants: {
        borderColor: ['responsive', 'hover', 'focus', 'group-hover'],
        cursor: ['responsive', 'disabled'],
        margin: ['responsive', 'focus'],
        opacity: ['responsive', 'hover', 'focus', 'group-hover'],
        padding: ['responsive', 'focus'],
        textColor: ['responsive', 'hover', 'focus', 'group-hover'],
    },
    plugins: [
        require('@tailwindcss/typography'),
        plugin(function ({ addComponents, config }) {
            addComponents({
                // same as: transition-all duration-300 ease-in-out
                '.transition-default': {
                    transitionProperty: config('theme.transitionProperty.all'),
                    transitionDuration: config('theme.transitionDuration.300'),
                    transitionTimingFunction: config('theme.transitionTimingFunction.in-out'),
                },
            });
        }),
    ],
};
