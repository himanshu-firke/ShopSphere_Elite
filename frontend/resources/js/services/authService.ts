import api from './api';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role: 'customer' | 'admin';
    profile?: {
        phone?: string;
        date_of_birth?: string;
        gender?: string;
        avatar?: string;
    };
    addresses?: Array<{
        id: number;
        type: 'billing' | 'shipping';
        first_name: string;
        last_name: string;
        company?: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        phone?: string;
        is_default: boolean;
    }>;
    created_at: string;
    updated_at: string;
}

export interface LoginRequest {
    email: string;
    password: string;
    remember?: boolean;
}

export interface RegisterRequest {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface AuthResponse {
    user: User;
    token: string;
    expires_at: string;
}

class AuthService {
    // Login user
    async login(credentials: LoginRequest): Promise<AuthResponse> {
        const response = await api.post('/auth/login', credentials);
        const { user, token } = response.data.data;
        
        // Store token and user in localStorage
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        
        return response.data.data;
    }

    // Register new user
    async register(userData: RegisterRequest): Promise<AuthResponse> {
        const response = await api.post('/auth/register', userData);
        const { user, token } = response.data.data;
        
        // Store token and user in localStorage
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        
        return response.data.data;
    }

    // Logout user
    async logout(): Promise<void> {
        try {
            await api.post('/auth/logout');
        } finally {
            // Clear local storage regardless of API response
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        }
    }

    // Get current user
    async getCurrentUser(): Promise<User> {
        const response = await api.get('/auth/me');
        return response.data.data;
    }

    // Refresh token
    async refreshToken(): Promise<AuthResponse> {
        const response = await api.post('/auth/refresh');
        const { user, token } = response.data.data;
        
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        
        return response.data.data;
    }

    // Forgot password
    async forgotPassword(email: string): Promise<void> {
        await api.post('/auth/forgot-password', { email });
    }

    // Reset password
    async resetPassword(token: string, email: string, password: string, passwordConfirmation: string): Promise<void> {
        await api.post('/auth/reset-password', {
            token,
            email,
            password,
            password_confirmation: passwordConfirmation
        });
    }

    // Check if user is authenticated
    isAuthenticated(): boolean {
        return !!localStorage.getItem('auth_token');
    }

    // Get stored user
    getStoredUser(): User | null {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    }

    // Get stored token
    getStoredToken(): string | null {
        return localStorage.getItem('auth_token');
    }
}

export default new AuthService();
