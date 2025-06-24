<template>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Login</h2>
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input
                        type="email"
                        id="email"
                        v-model="form.email"
                        required
                        :disabled="loading"
                    />
                    <span v-if="errors.email" class="error">{{ errors.email[0] }}</span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input
                        type="password"
                        id="password"
                        v-model="form.password"
                        required
                        :disabled="loading"
                    />
                    <span v-if="errors.password" class="error">{{ errors.password[0] }}</span>
                </div>
                
                <button type="submit" :disabled="loading" class="btn btn-primary">
                    {{ loading ? 'Logging in...' : 'Login' }}
                </button>
                
                <p class="auth-switch">
                    Don't have an account? 
                    <a href="#" @click.prevent="$emit('switch-mode', 'register')">Register here</a>
                </p>
            </form>
        </div>
    </div>
</template>

<script>
import api from '../services/api.js';

export default {
    name: 'LoginComponent',
    emits: ['login-success', 'switch-mode'],
    data() {
        return {
            form: {
                email: '',
                password: ''
            },
            errors: {},
            loading: false
        }
    },
    methods: {
        async handleLogin() {
            this.loading = true;
            this.errors = {};
            
            try {
                const response = await api.login(this.form);
                const { user, token } = response.data;
                
                // Store token and user
                api.setToken(token);
                api.setUser(user);
                
                this.$emit('login-success', user);
                
                // Reset form
                this.form = { email: '', password: '' };
                
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    alert('Login failed. Please try again.');
                }
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style scoped>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 20px;
}

.auth-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    width: 100%;
    max-width: 400px;
}

.auth-card h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #333;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.form-group input:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.btn {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
}

.btn:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.auth-switch {
    text-align: center;
    margin-top: 1rem;
    color: #666;
}

.auth-switch a {
    color: #007bff;
    text-decoration: none;
}

.auth-switch a:hover {
    text-decoration: underline;
}
</style>
