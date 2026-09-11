import { ref } from 'vue';
import { ApolloLink, Observable } from '@apollo/client/core';
import { GraphQlErrorCodes } from '@/common/lib';
import { MaintenanceError, maintenanceDetails, startMaintenance } from '@/common/maintenance';

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

// GraphQlErrorSubscriber sets UNAUTHENTICATED when access is denied because they're not signed in
// (FORBIDDEN if they are). Denied nullable fields (most queries) come back as warnings, not errors.
const isUnauthenticated = (result) => [...(result.errors ?? []), ...(result.extensions?.warnings ?? [])]
    .some((error) => GraphQlErrorCodes.UNAUTHENTICATED === error.extensions?.code);

/**
 * Who's signed in & how long until their session expires. See SecurityController::sessionInfo().
 * Plain fetch so it doesn't go through sessionLink. A GET doesn't extend the session.
 * During maintenance it opens the maintenance modal & throws a MaintenanceError.
 *
 * @param {'GET'|'POST'} method POST extends the session
 * @returns {Promise<{userId: string|null, remaining: number|null}>} remaining is null if it doesn't expire
 */
export const fetchSessionInfo = async (method = 'GET') => {
    // accepting JSON gets the maintenance response as JSON too
    const response = await fetch('/session-info', { method, headers: { accept: 'application/json' } });
    const body = await response.json();

    const details = maintenanceDetails(response.status, body);
    if (null !== details) {
        startMaintenance(details);

        throw new MaintenanceError();
    }

    if (!response.ok) {
        throw new Error(`Session info failed with a ${response.status}.`);
    }

    return body;
};

export const extendSession = () => fetchSessionInfo('POST');

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

        subscription = forward(operation).subscribe({
            next: (result) => {
                // nothing to hold for if they weren't signed in to begin with
                if (!rootStore?.loggedIn || !isUnauthenticated(result)) {
                    observer.next(result);

                    return;
                }

                held = true;
                heldRequests.add(send);
                expireSession();
            },
            error: (error) => observer.error(error),
            complete: () => {
                if (!held) {
                    observer.complete();
                }
            },
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
