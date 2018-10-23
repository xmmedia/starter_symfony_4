let files = [
    '/favicon.ico',
];

// have to do this here because the file doesn't actually exist
// when webpack is doing it's compilation
workbox.precaching.precache(files);