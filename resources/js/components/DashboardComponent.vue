<template>
    <div class="dashboard">
        <header class="dashboard-header">
            <h1>Welcome to Dashboard</h1>
            <div class="user-info">
                <span>Hello, {{ user.name }}!</span>
                <button @click="handleLogout" class="btn btn-logout" :disabled="loading">
                    {{ loading ? 'Logging out...' : 'Logout' }}
                </button>
            </div>
        </header>
        
        <main class="dashboard-content">
            <div class="card">
                <h2>User Information</h2>
                <div class="user-details">
                    <p><strong>Name:</strong> {{ user.name }}</p>
                    <p><strong>Email:</strong> {{ user.email }}</p>
                    <p><strong>Joined:</strong> {{ formatDate(user.created_at) }}</p>
                </div>
            </div>
            
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="actions">
                    <button class="btn btn-primary" @click="refreshUserData">
                        Refresh Profile
                    </button>
                    <button class="btn btn-secondary" @click="showApiInfo = !showApiInfo">
                        {{ showApiInfo ? 'Hide' : 'Show' }} API Info
                    </button>
                </div>
                
                <div v-if="showApiInfo" class="api-info">
                    <h3>API Token Info</h3>
                    <p><small>Your API token is stored in localStorage and included in requests automatically.</small></p>
                    <code>{{ api.getToken() ? 'Token: ' + api.getToken().substring(0, 20) + '...' : 'No token found' }}</code>
                </div>
            </div>
        </main>
    </div>
</template>

<script>
import api from '../services/api.js';

export default {
    name: 'DashboardComponent',
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    emits: ['logout'],
    data() {
        return {
            loading: false,
            showApiInfo: false,
            api: api
        }
    },
    methods: {
        async handleLogout() {
            this.loading = true;
            
            try {
                await api.logout();
                api.removeToken();
                this.$emit('logout');
            } catch (error) {
                console.error('Logout error:', error);
                // Even if API call fails, remove token locally
                api.removeToken();
                this.$emit('logout');
            } finally {
                this.loading = false;
            }
        },
        
        async refreshUserData() {
            try {
                const response = await api.getUser();
                this.$emit('user-updated', response.data.user);
            } catch (error) {
                console.error('Failed to refresh user data:', error);
                alert('Failed to refresh user data');
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString();
        }
    }
}
</script>

<style scoped>
body{
    margin: 0;
}
.dashboard {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem 0;
    border-bottom: 2px solid #e9ecef;
}

.dashboard-header h1 {
    margin: 0;
    color: #333;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-info span {
    font-weight: 500;
    color: #666;
}

.dashboard-content {
    display: grid;
    gap: 2rem;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #333;
}

.user-details p {
    margin: 0.5rem 0;
    color: #555;
}

.actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background-color: #545b62;
}

.btn-logout {
    background-color: #dc3545;
    color: white;
}

.btn-logout:hover:not(:disabled) {
    background-color: #c82333;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.api-info {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 4px solid #007bff;
}

.api-info h3 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    color: #333;
}

.api-info code {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-family: monospace;
    word-break: break-all;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .dashboard-content {
        grid-template-columns: 1fr;
    }
    
    .actions {
        flex-direction: column;
    }
}
</style>
