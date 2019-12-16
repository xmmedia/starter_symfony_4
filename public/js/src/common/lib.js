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

export const waitForValidation = function () {
    return new Promise((resolve) => {
        const unwatch = this.$watch(
            () => !this.$v.$pending,
            (isNotPending) => {
                if (isNotPending) {
                    if (unwatch) {
                        unwatch();
                    }
                    resolve(!this.$v.$invalid);
                }
            },
            { immediate: true }
        );
    })
};
