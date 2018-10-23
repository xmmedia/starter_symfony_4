export const logError = function (e) {
    if (console && e !== undefined) {
        console.error(e);
    }
};
