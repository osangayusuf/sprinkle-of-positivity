<?php

namespace App\Enums;

enum PushCategory: string
{
    case Chat = 'chat';
    case Insights = 'insights';

    /**
     * The users column holding a member's global on/off switch for this category.
     */
    public function preferenceColumn(): string
    {
        return match ($this) {
            self::Chat => 'push_chat_enabled',
            self::Insights => 'push_insights_enabled',
        };
    }
}
