const stateMachineDefaults = {
    id: 'component',
};

export const basic = {
    ...stateMachineDefaults,
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'ready',
                ERROR: 'error',
            },
        },
        ready: {
            type: 'final',
        },
        error: {
            type: 'final',
        },
    },
};

export const list = {
    ...stateMachineDefaults,
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                ERROR: 'error',
            },
        },
        loaded: {
            on: {
                UPDATE: 'loading',
            },
        },
        error: {
            on: {
                UPDATE: 'loading',
            },
        },
    },
};

export const view = {
    ...stateMachineDefaults,
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                NOT_FOUND: 'not_found',
                ERROR: 'error',
            },
        },
        loaded: {
            type: 'final',
        },
        not_found: {
            type: 'final',
        },
        error: {
            type: 'final',
        },
    },
};

export const viewWithDelete = {
    ...stateMachineDefaults,
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'loaded',
                NOT_FOUND: 'not_found',
                ERROR: 'error',
            },
        },
        loaded: {
            on: {
                DELETE: 'deleting',
            },
        },
        not_found: {
            type: 'final',
        },
        deleting: {
            on: {
                DELETED: 'deleted',
                ERROR: 'loaded',
            },
        },
        deleted: {
            type: 'final',
        },
        error: {
            type: 'final',
        },
    },
};

export const add = {
    ...stateMachineDefaults,
    initial: 'ready',
    states: {
        ready: {
            on: {
                SUBMIT: 'submitting',
            },
        },
        submitting: {
            on: {
                SUBMITTED: 'saved',
                ERROR: 'ready',
            },
        },
        saved: {
            type: 'final',
        },
    },
};

export const edit = {
    ...stateMachineDefaults,
    initial: 'loading',
    states: {
        loading: {
            on: {
                LOADED: 'ready',
                NOT_FOUND: 'not_found',
                ERROR: 'error',
            },
        },
        ready: {
            initial: 'ready',
            states: {
                ready: {
                    on: {
                        SAVE: 'saving',
                        DELETE: 'deleting',
                    },
                },
                saving: {
                    on: {
                        SAVED: 'saved',
                        ERROR: 'ready',
                    },
                },
                saved: {
                    type: 'final',
                },
                deleting: {
                    on: {
                        DELETED: 'deleted',
                        ERROR: 'ready',
                    },
                },
                deleted: {
                    type: 'final',
                },
            },
        },
        not_found: {
            type: 'final',
        },
        error: {
            type: 'final',
        },
    },
};
