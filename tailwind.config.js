const plugin = require('tailwindcss/plugin');
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    theme: {
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
