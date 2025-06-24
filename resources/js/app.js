import './bootstrap';
import { createApp } from 'vue';
import ExampleComponent from './components/ExampleComponent.vue';
import AuthApp from './components/AuthApp.vue';
import LoginComponent from './components/LoginComponent.vue';
import RegisterComponent from './components/RegisterComponent.vue';
import DashboardComponent from './components/DashboardComponent.vue';
import ProductsComponent from './components/ProductsComponent.vue';

// Create Vue app instance
const app = createApp({});

// Register Vue components
app.component('example-component', ExampleComponent);
app.component('auth-app', AuthApp);
app.component('login-component', LoginComponent);
app.component('register-component', RegisterComponent);
app.component('dashboard-component', DashboardComponent);
app.component('products-component', ProductsComponent);

// Mount the app to an element with id="app"
app.mount('#app');
