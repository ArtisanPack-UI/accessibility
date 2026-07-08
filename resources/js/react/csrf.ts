/**
 * Header helper shared by the React trigger surfaces.
 *
 * The shipped JSON endpoints (POST /api/v1/a11y/ai/*) sit behind
 * Laravel's `auth:sanctum` + `throttle:api` middleware, which in a
 * stateful SPA context requires the `X-XSRF-TOKEN` header replayed from
 * the `XSRF-TOKEN` cookie plus an `X-Requested-With: XMLHttpRequest`
 * header so Laravel treats the request as JSON and returns 401/419 as
 * proper JSON rather than the login redirect HTML.
 *
 * @since 2.2.0
 */

/**
 * Build the fetch headers a request to the shipped AI endpoints needs.
 *
 * Callers can pass extra headers; anything they pass wins.
 */
export function ai11yHeaders(extra?: Record<string, string>): Record<string, string> {
    const base: Record<string, string> = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const token = readXsrfCookie();

    if (token !== null) {
        base['X-XSRF-TOKEN'] = token;
    }

    return { ...base, ...(extra ?? {}) };
}

function readXsrfCookie(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const cookies = document.cookie.split(';');

    for (const cookie of cookies) {
        const [rawName, ...rest] = cookie.trim().split('=');
        if (rawName === 'XSRF-TOKEN') {
            try {
                return decodeURIComponent(rest.join('='));
            } catch {
                return null;
            }
        }
    }

    return null;
}
