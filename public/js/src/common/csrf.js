let csrfTokenValue = null;

/**
 * Returns the stateless CSRF token to send back in the csrf-token header or the _csrf_token
 * field on POSTs.
 *
 * Every call also (re)writes the token as a cookie, which is the half Symfony actually checks
 * the header/field against. That's a side effect, not a one time setup: Symfony clears the
 * cookie after each validated request, so call this again right before anything that depends
 * on it – see the login form's @submit, which has no header to fall back on. The token itself
 * is only generated once, so the re-set cookie still matches an already rendered field.
 *
 * Matches what Symfony's csrf_protection Stimulus controller does.
 *
 * @returns {string}
 */
export const csrfToken = () => {
    // must match framework.csrf_protection.cookie_name
    const csrfCookieName = 'csrf-token';

    if (!csrfTokenValue) {
        // 18 bytes because it's divisible by 3, so base64 adds no "=" padding, which isn't
        // legal in the cookie name below. Encodes to 24 characters, Symfony's minimum.
        csrfTokenValue = btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(18))));
    }

    // Symfony clears the cookie once it's been used, so it's always (re)set
    const cookie = `${csrfCookieName}_${csrfTokenValue}=${csrfCookieName}; path=/; samesite=strict`;
    document.cookie = 'https:' === window.location.protocol ? `__Host-${cookie}; secure` : cookie;

    return csrfTokenValue;
};
