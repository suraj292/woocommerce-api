<template>
    <div class="auth-app">
        <!-- Loading state -->
        <div v-if="initializing" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading...</p>
        </div>
        
        <!-- Main content -->
        <div v-else>
            <!-- Authenticated user dashboard -->
            <dashboard-component 
                v-if="isAuthenticated && currentUser"
                :user="currentUser"
                @logout="handleLogout"
                @user-updated="updateUser"
            />
            
            <!-- Authentication forms -->
            <div v-else>
                <!-- Login form -->
                <login-component 
                    v-if="currentMode === 'login'"
                    @login-success="handleAuthSuccess"
                    @switch-mode="switchMode"
                />
                
                <!-- Register form -->
                <register-component 
                    v-if="currentMode === 'register'"
                    @register-success="handleAuthSuccess"
                    @switch-mode="switchMode"
                />
            </div>
        </div>
        
        <!-- Success message -->
        <div v-if="successMessage" class="success-message">
            {{ successMessage }}
        </div>
    </div>
</template>

<script>
import LoginComponent from './LoginComponent.vue';
import RegisterComponent from './RegisterComponent.vue';
import DashboardComponent from './DashboardComponent.vue';
import api from '../services/api.js';

export default {
    name: 'AuthApp',
    components: {
        LoginComponent,
        RegisterComponent,
        DashboardComponent
    },
    data() {
        return {
            currentUser: null,
            currentMode: 'login', // 'login' or 'register'
            initializing: true,
            successMessage: ''
        }
    },
    computed: {
        isAuthenticated() {
            return !!this.currentUser && !!api.getToken();
        }
    },
    async mounted() {
        await this.checkAuthStatus();
    },
    methods: {
        async checkAuthStatus() {
            const token = api.getToken();
            const storedUser = api.getStoredUser();
            
            if (token && storedUser) {
                try {
                    // Verify token is still valid by fetching user data
                    const response = await api.getUser();
                    this.currentUser = response.data.user;
                    api.setUser(this.currentUser); // Update stored user data
                } catch (error) {
                    // Token is invalid, clear stored data
                    api.removeToken();
                    this.currentUser = null;
                }
            }
            
            this.initializing = false;
        },
        
        handleAuthSuccess(user) {
            this.currentUser = user;
            this.showSuccessMessage('Authentication successful!');
        },
        
        handleLogout() {
            this.currentUser = null;
            this.currentMode = 'login';
            this.showSuccessMessage('Logged out successfully!');
        },
        
        switchMode(mode) {
            this.currentMode = mode;
        },
        
        updateUser(user) {
            this.currentUser = user;
            api.setUser(user);
        },
        
        showSuccessMessage(message) {
            this.successMessage = message;
            setTimeout(() => {
                this.successMessage = '';
            }, 3000);
        }
    }
}
</script>

<style scoped>
body {
    margin: 0;
    padding: 0;
}
.auth-app {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.loading-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: white;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.success-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Global styles for child components */
:deep(.auth-container) {
    background: transparent;
}

:deep(.dashboard) {
    background: white;
    min-height: 100vh;
    padding-top: 0;
}
</style>
