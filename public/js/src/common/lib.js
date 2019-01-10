export const logError = function (e) {
    if (console && e !== undefined) {
        console.error(e);
    }
};

export const uuid4 = function () {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    )
};

export const hasGraphQlError = function (e) {
    return e && e.graphQLErrors && e.graphQLErrors[0] && e.graphQLErrors[0].validation;
};
