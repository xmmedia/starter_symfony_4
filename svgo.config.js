module.exports = {
    plugins: [
        {
            name: 'preset-default',
            params: {
                overrides: {
                    removeUselessDefs: false,
                    cleanupIDs: false,
                },
            },
        },
    ],
};
