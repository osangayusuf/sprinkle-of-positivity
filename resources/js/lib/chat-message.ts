import type { ChatMention } from '@/types/models';

export type MessageSegment =
    | { type: 'text'; text: string }
    | { type: 'link'; text: string; href: string }
    | { type: 'mention'; text: string };

// A mention token (`@[12]`), `@everyone`, or a URL (http(s):// or www.) up to
// the next space.
const TOKEN_PATTERN =
    /@\[(\d+)\]|\B@everyone\b|\b(?:https?:\/\/|www\.)[^\s<]+/gi;
const TRAILING_PUNCTUATION = /[.,!?;:'"]+$/;

function trimUrl(url: string): string {
    let trimmed = url.replace(TRAILING_PUNCTUATION, '');

    while (trimmed.endsWith(')') && !trimmed.includes('(')) {
        trimmed = trimmed.slice(0, -1).replace(TRAILING_PUNCTUATION, '');
    }

    return trimmed;
}

/**
 * Split a chat message body into plain text, clickable links and mentions.
 * Mention tokens whose user isn't in `mentions` stay as plain text. Links are
 * always http(s), so a `javascript:` URL can never become one.
 */
export function parseMessageBody(
    body: string,
    mentions: ChatMention[],
): MessageSegment[] {
    const segments: MessageSegment[] = [];
    let cursor = 0;

    const pushText = (end: number) => {
        if (end > cursor) {
            segments.push({ type: 'text', text: body.slice(cursor, end) });
        }
    };

    for (const match of body.matchAll(TOKEN_PATTERN)) {
        const start = match.index;

        if (start < cursor) {
            continue;
        }

        if (match[1] !== undefined) {
            const mentioned = mentions.find(
                (mention) => mention.id === Number(match[1]),
            );

            if (!mentioned) {
                continue;
            }

            pushText(start);
            segments.push({ type: 'mention', text: `@${mentioned.name}` });
            cursor = start + match[0].length;

            continue;
        }

        if (match[0].toLowerCase() === '@everyone') {
            pushText(start);
            segments.push({ type: 'mention', text: '@everyone' });
            cursor = start + match[0].length;

            continue;
        }

        const url = trimUrl(match[0]);

        if (url === '') {
            continue;
        }

        pushText(start);
        segments.push({
            type: 'link',
            text: url,
            href: /^www\./i.test(url) ? `https://${url}` : url,
        });
        cursor = start + url.length;
    }

    pushText(body.length);

    return segments;
}

/**
 * The reverse of `serializeMentions`: show `@Name` for each `@[id]` token of a
 * member in `mentions`, for putting a saved body back into the composer.
 */
export function displayMentions(body: string, mentions: ChatMention[]): string {
    return body.replace(/@\[(\d+)\]/g, (token, id) => {
        const mentioned = mentions.find((mention) => mention.id === Number(id));

        return mentioned ? `@${mentioned.name}` : token;
    });
}

function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Turn the `@Name` text the composer shows into the `@[id]` tokens the server
 * stores, for the members picked from the suggestion list.
 */
export function serializeMentions(
    draft: string,
    tagged: ChatMention[],
): string {
    return [...tagged]
        .sort((a, b) => b.name.length - a.name.length)
        .reduce(
            (body, member) =>
                body.replace(
                    new RegExp(
                        `@${escapeRegExp(member.name)}(?=$|\\s|[.,!?;:])`,
                        'g',
                    ),
                    `@[${member.id}]`,
                ),
            draft,
        );
}
