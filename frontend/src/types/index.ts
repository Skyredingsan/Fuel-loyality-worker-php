export type UserRole = 'tm' | 'expert' | 'coordinator';

export interface User {
    id: number;
    email: string;
    role: UserRole;
    role_label?: string;
    fio: string;
    cluster_name?: string | null;
    azs_count: number;
    created_at?: string;
    updated_at?: string;
}

export interface LoginRequest {
    email: string;
    password: string;
}

export interface LoginResponse {
    token: string;
    token_type: string;
    expires_in: number;
    user: User;
}

export type IndicatorType = 'base' | 'extra' | 'penalty';

export interface KpiCategory {
    id: number;
    code: string;
    name: string;
    description?: string;
}

export interface KpiIndicator {
    id: number;
    category_id: number;
    category_code?: string;
    category_name?: string;
    code: string;
    name: string;
    description?: string;
    unit: string;
    indicator_type: IndicatorType;
    base_value?: number | null;
    base_weight?: number | null;
    extra_weight?: number | null;
    penalty_weight?: number | null;
}

export type ResultStatus = 'draft' | 'confirmed';

export interface IndicatorResultInput {
    indicator_code: string;
    fact_value?: number | null;
    document_url?: string | null;
}

export interface EnterResultRequest {
    user_id: number;
    period: string;
    results: IndicatorResultInput[];
}

export interface CategorySummary {
    category_code: string;
    category_name: string;
    base_points: number;
    extra_points: number;
    penalty_points: number;
    total_points: number;
}

export interface Level {
    id: number;
    name: string;
    min_points_per_year: number;
    privileges: Record<string, any>;
}

export interface FullResultSummary {
    user_id: number;
    user_fio: string;
    period: string;
    categories: CategorySummary[];
    total_points: number;
    yearly_points: number;
    level?: Level | null;
    detailed_results?: any[];
}

export interface CsvExport {
    id: string;
    status: 'pending' | 'processing' | 'ready' | 'failed';
    period: string;
    rows_count: number;
    error?: string | null;
    download_url?: string | null;
    created_at?: string;
    updated_at?: string;
}