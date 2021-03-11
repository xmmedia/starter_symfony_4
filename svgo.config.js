const { extendDefaultPlugins } = require('svgo');

module.exports = {
    plugins: extendDefaultPlugins([
        {
            name: 'removeUselessDefs',
            active: false,
        },
        {
            name: 'cleanupIDs',
            active: false,
        },
    ]),
};
