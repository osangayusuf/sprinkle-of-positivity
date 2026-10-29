import { parseMessageBody } from '@/lib/chat-message';
import type { ChatMention } from '@/types/models';

/**
 * A chat message or comment body with its links made clickable and its
 * mentions highlighted. Text and links inherit the surrounding colour.
 */
export function MessageBody({
    body,
    mentions,
}: {
    body: string;
    mentions: ChatMention[];
}) {
    return (
        <>
            {parseMessageBody(body, mentions).map((segment, index) => {
                if (segment.type === 'link') {
                    return (
                        <a
                            key={index}
                            href={segment.href}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="break-all underline underline-offset-2"
                        >
                            {segment.text}
                        </a>
                    );
                }

                if (segment.type === 'mention') {
                    return (
                        <span key={index} className="font-semibold">
                            {segment.text}
                        </span>
                    );
                }

                return segment.text;
            })}
        </>
    );
}
