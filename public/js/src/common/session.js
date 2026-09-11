import { ref } from 'vue';
import { ApolloLink, Observable } from '@apollo/client/core';

/**
 * Set when the user is found to be signed out, such as after signing out in another window
 * or their session ending. session_expired.vue shows a modal while it's set.
 */
export const sessionExpired = ref(false);
// the modal can be dismissed (a second Escape can't be prevented), so it's reopened on the next held request
export const sessionModalOpen = ref(false);

// the app's root store (admin or user), which holds the signed in user
let rootStore = null;

export const setSessionStore = (store) => {
    rootStore = store;
};

export const useSessionStore = () => rootStore;

// requests rejected because the user was signed out, re-sent once they've signed back in
const heldRequests = new Set();

// GraphQlErrorSubscriber sets the code to 401 on access denied errors.
// Denied nullable fields (most queries) come back as warnings instead of errors.
const isAccessDenied = (result) => [...(result.errors ?? []), ...(result.extensions?.warnings ?? [])]
    .some((error) => 401 === error.code);

/**
 * Who's signed in & how long until their session expires. See SecurityController::sessionInfo().
 * Plain fetch so it doesn't go through sessionLink. A GET doesn't extend the session.
 *
 * @param {'GET'|'POST'} method POST extends the session
 * @returns {Promise<{userId: string|null, remaining: number|null}>} remaining is null if it doesn't expire
 */
export const fetchSessionInfo = async (method = 'GET') => {
    const response = await fetch('/session-info', { method });

    return response.json();
};

export const extendSession = () => fetchSessionInfo('POST');

// access denied is also returned when signed in without the required role, so confirm they're signed out.
// Shared by requests rejected at the same time, so there's only one check.
let signedOutCheck = null;
const isSignedOut = () => {
    if (!signedOutCheck) {
        signedOutCheck = fetchSessionInfo()
            .then(({ userId }) => null === userId, () => false)
            .finally(() => {
                signedOutCheck = null;
            });
    }

    return signedOutCheck;
};

export const expireSession = () => {
    sessionExpired.value = true;
    sessionModalOpen.value = true;
};

/**
 * Holds any request that's rejected because the user is signed out, instead of passing the error
 * on to the component, & opens the session expired modal. The request is re-sent once they sign
 * back in, so the component carries on as if nothing happened.
 *
 * Must be before csrfLink so a re-sent request gets the CSRF cookie set again.
 */
export const sessionLink = new ApolloLink((operation, forward) => new Observable((observer) => {
    let subscription;

    const send = () => {
        let held = false;
        let checked = Promise.resolve();

        subscription = forward(operation).subscribe({
            next: (result) => {
                // nothing to hold for if they weren't signed in to begin with
                if (!rootStore?.loggedIn || !isAccessDenied(result)) {
                    observer.next(result);

                    return;
                }

                checked = isSignedOut().then((signedOut) => {
                    if (signedOut) {
                        held = true;
                        heldRequests.add(send);
                        expireSession();

                        return;
                    }

                    observer.next(result);
                });
            },
            error: (error) => observer.error(error),
            complete: () => checked.then(() => {
                if (!held) {
                    observer.complete();
                }
            }),
        });
    };

    send();

    return () => {
        heldRequests.delete(send);
        subscription.unsubscribe();
    };
}));

export const resumeSession = () => {
    sessionExpired.value = false;
    sessionModalOpen.value = false;

    const requests = [...heldRequests];
    heldRequests.clear();
    requests.forEach((send) => send());
};
