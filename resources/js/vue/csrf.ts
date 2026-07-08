/**
 * Header helper shared by the Vue trigger surfaces.
 *
 * The shipped JSON endpoints (POST /api/v1/a11y/ai/*) sit behind
 * Laravel's `auth:sanctum` + `throttle:api` middleware; a stateful SPA
 * request needs the `X-XSRF-TOKEN` header replayed from the
 * `XSRF-TOKEN` cookie plus `X-Requested-With: XMLHttpRequest` so
 * Laravel returns 401/419 as JSON rather than an HTML login redirect.
 *
 * @since 2.2.0
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

    for (const cookie of document.cookie.split(';')) {
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
