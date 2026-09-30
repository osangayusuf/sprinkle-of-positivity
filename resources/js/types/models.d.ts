export type GroupMembershipRole = 'manager' | 'member';

export type GroupMembershipStatus =
    | 'pending'
    | 'approved'
    | 'rejected'
    | 'removed';

export type Group = {
    id: number;
    name: string;
    slug: string;
    purpose: string;
    cover_image_url: string | null;
    duration_days: number | null;
    starts_on: string | null;
    status: 'active' | 'archived';
    is_private: boolean;
    members_count?: number;
    current_day: number | null;
};

export type GroupMembershipInfo = {
    role: GroupMembershipRole;
    status: GroupMembershipStatus;
};

export type GroupMember = {
    membership_id: number;
    user_id: number;
    name: string;
    avatar: string | null;
    role: GroupMembershipRole;
    status: GroupMembershipStatus;
    applied_at: string | null;
};

export type Verse = {
    id: number;
    date: string;
    reference: string;
    text: string;
    image_url: string | null;
};

export type ReactionSummary = {
    emojis: string[];
    count: number;
};

export type InsightAuthor = {
    id: number;
    name: string;
    avatar: string | null;
};

export type Insight = {
    id: number;
    user: InsightAuthor;
    body: string;
    image_url: string | null;
    created_at: string;
    comments_count?: number;
    reaction_summary: ReactionSummary;
    my_reaction: string | null;
};

export type Comment = {
    id: number;
    user: InsightAuthor;
    body: string;
    created_at: string;
    reaction_summary: ReactionSummary;
    my_reaction: string | null;
    replies: Comment[];
};

export type QuizOption = {
    id: number;
    label: string;
};

export type Notification = {
    id: string;
    title: string;
    body: string;
    read: boolean;
    created_at: string;
};

export type Quiz = {
    id: number;
    question: string;
    created_by_name: string;
    created_by_avatar: string | null;
    created_at: string;
    options: QuizOption[];
    responses_count: number;
    my_response_option_id: number | null;
    correct_quiz_option_id: number | null;
};

export type LeaderboardEntry = {
    rank: number;
    id: number;
    name: string;
    avatar: string | null;
    points: number;
    progress: number;
    is_me: boolean;
};

export type Level = {
    key: string;
    label: string;
    threshold: number;
};

export type MarketplaceListing = {
    id: number;
    title: string;
    description: string | null;
    image_url: string | null;
    is_active: boolean;
    cta_label: string | null;
    cta_url: string | null;
    position: number;
    starts_at: string | null;
    ends_at: string | null;
};

export type ChallengeProgress = {
    completed_days: number[];
    completed_count: number;
    current_streak: number;
    longest_streak: number;
    completed_today: boolean;
    required_days: number[];
    missing_days: number[];
    eligible: boolean;
};

export type Certificate = {
    id: number;
    code: string;
    recipient_name: string;
    duration_days: number;
    year: number;
    issued_at: string;
    revoked: boolean;
    group_name?: string;
    override_reason?: string | null;
    revoke_reason?: string | null;
};
