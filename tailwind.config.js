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
    ],
};
