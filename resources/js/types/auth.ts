export type User = {
    id: number;
    name: string;
    email: string;
    username?: string | null;
    /** Điểm tài khoản */
    point?: number;
    bank_name?: string | null;
    bank_account_number?: string | null;
    bank_account_holder?: string | null;
    is_admin?: boolean;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
