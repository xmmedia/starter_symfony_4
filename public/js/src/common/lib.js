export const logError = function (e) {
    if (console && e !== undefined) {
        console.error(e);
    }
};

export const hasGraphQlError = function (e) {
    return e && e.graphQLErrors && e.graphQLErrors[0];
};

export const hasGraphQlValidationError = function (e) {
    return e && e.graphQLErrors && e.graphQLErrors[0] && e.graphQLErrors[0].validation;
};
