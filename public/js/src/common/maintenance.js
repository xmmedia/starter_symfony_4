import { ref } from 'vue';
import { ApolloLink, Observable } from '@apollo/client/core';
import { GraphQlErrorCodes } from '@/common/lib';

/**
 * Set to { message, until } while the site's in maintenance mode, null otherwise.
 * maintenance.vue shows a modal while it's set. See the bundle's MaintenanceSubscriber.
 */
export const maintenance = ref(null);
// the modal can be dismissed (a second Escape can't be prevented), so it's reopened on the next held request
export const maintenanceModalOpen = ref(false);

// requests rejected because of maintenance, re-sent once it's over
const heldRequests = new Set();

/**
 * Thrown when a request is answered with the maintenance response, instead of the result.
 */
export class MaintenanceError extends Error {
    constructor () {
        super('The site is in maintenance mode.');
        this.name = 'MaintenanceError';
    }
}

/**
 * @param {number} status the HTTP status
 * @param {object|undefined} body the parsed JSON response
 * @returns {{message: string, until: string|null}|null} null if it's not the maintenance response
 */
export const maintenanceDetails = (status, body) => {
    const error = body?.errors?.[0];

    if (503 !== status || GraphQlErrorCodes.MAINTENANCE !== error?.extensions?.code) {
        return null;
    }

    return {
        message: error.message,
        until: error.extensions.until ?? null,
    };
};

// Apollo's network error for a non-2xx response has the status & the parsed body
export const isMaintenanceError = (networkError) => {
    return null !== maintenanceDetails(networkError?.statusCode, networkError?.result);
};

export const startMaintenance = (details) => {
    // opened only as it starts, so a check while it's dismissed doesn't reopen it
    if (!maintenance.value) {
        maintenanceModalOpen.value = true;
    }

    maintenance.value = details;
};

/**
 * Holds any request that's rejected because the site's in maintenance mode, instead of passing
 * the error on to the component, & opens the maintenance modal. The request is re-sent once
 * it's over, so the component carries on as if nothing happened.
 *
 * Must be before sessionLink & csrfLink, so a re-sent request goes through them again.
 */
export const maintenanceLink = new ApolloLink((operation, forward) => new Observable((observer) => {
    let subscription;

    const send = () => {
        subscription = forward(operation).subscribe({
            next: (result) => observer.next(result),
            error: (error) => {
                const details = maintenanceDetails(error?.statusCode, error?.result);

                if (null === details) {
                    observer.error(error);

                    return;
                }

                heldRequests.add(send);
                startMaintenance(details);
                maintenanceModalOpen.value = true;
            },
            complete: () => observer.complete(),
        });
    };

    send();

    return () => {
        heldRequests.delete(send);
        subscription.unsubscribe();
    };
}));

export const endMaintenance = () => {
    maintenance.value = null;
    maintenanceModalOpen.value = false;

    const requests = [...heldRequests];
    heldRequests.clear();
    requests.forEach((send) => send());
};
