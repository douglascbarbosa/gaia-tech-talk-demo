export type Developer = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    github_profile?: string | null;
    status: 'open_for_new_jobs' | 'working';
    address_street?: string | null;
    address_city?: string | null;
    address_postal_code?: string | null;
    address_country?: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    developer: Developer | null;
    /** Same as `developer` when logged in; kept for Wayfinder and Inertia defaults. */
    user: Developer | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
