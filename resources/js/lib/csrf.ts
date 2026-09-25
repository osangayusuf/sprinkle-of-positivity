/**
 * Laravel's session CSRF cookie, for authenticating plain `fetch()` calls
 * that (unlike Inertia's own visits) don't get the header added for free.
 */
export function csrfHeader(): Record<string, string> {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);

    return match ? { 'X-XSRF-TOKEN': decodeURIComponent(match[1]) } : {};
}
