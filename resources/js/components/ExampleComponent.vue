<template>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Vue.js Component</h3>
            </div>
            <div class="card-body">
                <p>{{ message }}</p>
                <button @click="updateMessage" class="btn btn-primary">
                    Click me!
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ExampleComponent',
    data() {
        return {
            message: 'Hello from Vue.js!'
        }
    },
    methods: {
        updateMessage() {
            this.message = 'Button clicked! Vue.js is working!'
        }
    }
}
</script>

<style scoped>
.container {
    max-width: 600px;
    margin: 50px auto;
}

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
}

.card-header h3 {
    margin: 0;
    color: #333;
}

.card-body {
    padding: 1rem;
}

.btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.btn:hover {
    background-color: #0056b3;
}
</style>
