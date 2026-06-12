/**
 * Encode a binary buffer to base64url (URL-safe base64, no padding).
 * Used for WebAuthn credential IDs and challenge encoding.
 */
export function bufferToBase64url (buffer) {
    const bytes = new Uint8Array(buffer);
    let str = '';
    for (const byte of bytes) {
        str += String.fromCharCode(byte);
    }
    return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}

/**
 * Decode a base64url string to an ArrayBuffer.
 */
export function base64urlToBuffer (base64url) {
    const padding = '='.repeat((4 - (base64url.length % 4)) % 4);
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/') + padding;
    const str = atob(base64);
    const buffer = new ArrayBuffer(str.length);
    const bytes = new Uint8Array(buffer);
    for (let i = 0; i < str.length; i++) {
        bytes[i] = str.charCodeAt(i);
    }
    return buffer;
}

/**
 * Prepare creation options received from server for use with navigator.credentials.create().
 * Converts base64url-encoded fields to ArrayBuffer.
 */
export function prepareCreationOptions (options) {
    return {
        ...options,
        challenge: base64urlToBuffer(options.challenge),
        user: {
            ...options.user,
            id: base64urlToBuffer(options.user.id),
        },
        excludeCredentials: (options.excludeCredentials || []).map((c) => ({
            ...c,
            id: base64urlToBuffer(c.id),
        })),
    };
}

/**
 * Prepare assertion/request options received from server for use with navigator.credentials.get().
 * Converts base64url-encoded fields to ArrayBuffer.
 */
export function prepareRequestOptions (options) {
    return {
        ...options,
        challenge: base64urlToBuffer(options.challenge),
        allowCredentials: (options.allowCredentials || []).map((c) => ({
            ...c,
            id: base64urlToBuffer(c.id),
        })),
    };
}

/**
 * Encode a PublicKeyCredential (attestation or assertion) for sending to the server.
 * Converts ArrayBuffer fields to base64url strings.
 */
export function encodeCredential (credential) {
    const response = credential.response;
    const encoded = {
        id:    credential.id,
        rawId: bufferToBase64url(credential.rawId),
        type:  credential.type,
    };

    if (response instanceof AuthenticatorAttestationResponse) {
        encoded.response = {
            clientDataJSON:    bufferToBase64url(response.clientDataJSON),
            attestationObject: bufferToBase64url(response.attestationObject),
        };
        if (response.getTransports) {
            encoded.response.transports = response.getTransports();
        }
    } else {
        // AuthenticatorAssertionResponse
        encoded.response = {
            clientDataJSON:    bufferToBase64url(response.clientDataJSON),
            authenticatorData: bufferToBase64url(response.authenticatorData),
            signature:         bufferToBase64url(response.signature),
            userHandle:        response.userHandle ? bufferToBase64url(response.userHandle) : null,
        };
    }

    if (credential.authenticatorAttachment) {
        encoded.authenticatorAttachment = credential.authenticatorAttachment;
    }

    return encoded;
}
