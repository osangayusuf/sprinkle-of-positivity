const MINUTE = 60;
const HOUR = MINUTE * 60;
const DAY = HOUR * 24;

/**
 * A short relative-time label ("2m ago", "3h ago", "5d ago"), falling back
 * to a short date once it's more than a week old — matches the timestamps
 * shown throughout the Insights/Q&A/Notifications screens.
 */
export function formatRelativeTime(isoDate: string): string {
    const seconds = Math.max(
        0,
        Math.floor((Date.now() - new Date(isoDate).getTime()) / 1000),
    );

    if (seconds < MINUTE) {
        return 'Just now';
    }

    if (seconds < HOUR) {
        return `${Math.floor(seconds / MINUTE)}m ago`;
    }

    if (seconds < DAY) {
        return `${Math.floor(seconds / HOUR)}h ago`;
    }

    if (seconds < DAY * 7) {
        return `${Math.floor(seconds / DAY)}d ago`;
    }

    return new Date(isoDate).toLocaleDateString(undefined, {
        day: '2-digit',
        month: 'short',
    });
}
