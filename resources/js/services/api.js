import axios from 'axios';

// Create axios instance
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add token to requests if available
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle token expiration
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/vue-demo';
        }
        return Promise.reject(error);
    }
);

export default {
    // Auth endpoints
    register(userData) {
        return api.post('/register', userData);
    },
    
    login(credentials) {
        return api.post('/login', credentials);
    },
    
    logout() {
        return api.post('/logout');
    },
    
    getUser() {
        return api.get('/user');
    },
    
    // Helper methods
    setToken(token) {
        localStorage.setItem('auth_token', token);
    },
    
    getToken() {
        return localStorage.getItem('auth_token');
    },
    
    removeToken() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    },
    
    setUser(user) {
        localStorage.setItem('user', JSON.stringify(user));
    },
    
    getStoredUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }
};
