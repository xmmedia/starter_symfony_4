const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        specPattern: 'cypress/e2e/**/*.{cy,spec}.{js,jsx,ts,tsx}',
        // @todo-symfony
        baseUrl: 'https://dev.example.com',
    },
})
