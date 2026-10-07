export type User = {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
    isAdmin: boolean;
    isPartner: boolean;
};

/**
 * Shared auth props on pages that sit behind the `auth` middleware, where a
 * signed-in user is guaranteed.
 */
export type AuthenticatedAuth = Auth & {
    user: User;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
