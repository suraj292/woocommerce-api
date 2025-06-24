<template>
    <div class="products-section">
        <div class="section-header">
            <h2>WooCommerce Products</h2>
            <div class="header-actions">
                <button @click="showCreateForm = true" class="btn btn-primary">
                    Create New Product
                </button>
                <button @click="loadProducts" class="btn btn-secondary" :disabled="loading">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading products...</p>
        </div>

        <!-- Filters Section -->
        <div v-else class="filters-section">
            <div class="search-box">
                <input 
                    type="text" 
                    v-model="searchQuery" 
                    placeholder="Search products..."
                    @input="debouncedSearch"
                />
            </div>
        </div>

        <!-- Products Grid -->
        <div v-if="products.length === 0 && !loading" class="empty-state">
            <p>No products found.</p>
            <small>Create your first product using the "Create New Product" button.</small>
        </div>

        <div v-else-if="!loading" class="products-grid">
            <div 
                v-for="product in products" 
                :key="product.id" 
                class="product-card"
            >
                <div class="product-image">
                    <img 
                        :src="product.images && product.images[0] ? product.images[0].src : '/placeholder-image.svg'" 
                        :alt="product.name"
                        @error="handleImageError"
                    />
                </div>
                <div class="product-info">
                    <h3>{{ product.name }}</h3>
                    <div class="product-description" v-html="product.short_description || product.description"></div>
                    <div class="product-meta">
                        <span class="price">${{ product.regular_price || product.price || '0.00' }}</span>
                        <span :class="['status', product.status]">{{ product.status }}</span>
                    </div>
                    <div class="product-actions">
                        <button 
                            @click="editProduct(product)" 
                            class="btn btn-sm btn-secondary"
                            :disabled="updating === product.id"
                        >
                            {{ updating === product.id ? 'Loading...' : 'Edit' }}
                        </button>
                        <button 
                            @click="deleteProduct(product)" 
                            class="btn btn-sm btn-danger"
                            :disabled="deleting === product.id"
                        >
                            {{ deleting === product.id ? 'Deleting...' : 'Delete' }}
                        </button>
                        <a :href="product.permalink" target="_blank" class="btn btn-sm btn-outline">
                            View in Store
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="products.length > 0" class="pagination">
            <button 
                @click="loadProducts(currentPage - 1)" 
                :disabled="currentPage <= 1 || loading"
                class="btn btn-sm"
            >
                Previous
            </button>
            <span class="page-info">Page {{ currentPage }}</span>
            <button 
                @click="loadProducts(currentPage + 1)" 
                :disabled="products.length < perPage || loading"
                class="btn btn-sm"
            >
                Next
            </button>
        </div>

        <!-- Create/Edit Product Modal -->
        <div v-if="showCreateForm || editingProduct" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3>{{ editingProduct ? 'Edit Product' : 'Create New Product' }}</h3>
                    <button @click="closeModal" class="close-btn">&times;</button>
                </div>
                
                <form @submit.prevent="saveProduct" class="product-form">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            v-model="productForm.name" 
                            required 
                            :disabled="saving"
                        />
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            id="description" 
                            v-model="productForm.description"
                            rows="4"
                            :disabled="saving"
                        ></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price *</label>
                            <input 
                                type="number" 
                                id="price" 
                                v-model="productForm.price" 
                                step="0.01" 
                                min="0" 
                                required
                                :disabled="saving"
                            />
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" v-model="productForm.status" :disabled="saving">
                                <option value="draft">Draft</option>
                                <option value="publish">Published</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input 
                            type="url" 
                            id="image_url" 
                            v-model="productForm.image_url"
                            :disabled="saving"
                        />
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" @click="closeModal" class="btn btn-secondary" :disabled="saving">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : (editingProduct ? 'Update Product' : 'Create Product') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="message" :class="['message', messageType]">
            {{ message }}
        </div>
    </div>
</template>

<script>
import api from '../services/api.js';

export default {
    name: 'ProductsComponent',
    data() {
        return {
            loading: false,
            products: [],
            showCreateForm: false,
            editingProduct: null,
            saving: false,
            updating: null,
            deleting: null,
            message: '',
            messageType: 'success',
            searchQuery: '',
            currentPage: 1,
            perPage: 10,
            productForm: {
                name: '',
                description: '',
                price: '',
                image_url: '',
                status: 'draft'
            }
        }
    },
    async mounted() {
        await this.loadProducts();
    },
    methods: {
        async loadProducts(page = 1) {
            this.loading = true;
            try {
                const params = {
                    page: page,
                    per_page: this.perPage
                };
                
                if (this.searchQuery) {
                    params.search = this.searchQuery;
                }
                
                const response = await api.getProducts(params);
                this.products = response.data.products;
                this.currentPage = page;
            } catch (error) {
                this.showMessage('Failed to load products: ' + (error.response?.data?.message || error.message), 'error');
                console.error('API Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async saveProduct() {
            this.saving = true;
            try {
                let response;
                if (this.editingProduct) {
                    response = await api.updateProduct(this.editingProduct.id, this.productForm);
                } else {
                    response = await api.createProduct(this.productForm);
                }
                
                this.showMessage(response.data.message, 'success');
                this.closeModal();
                await this.loadProducts();
            } catch (error) {
                this.showMessage('Failed to save product: ' + (error.response?.data?.message || error.message), 'error');
            } finally {
                this.saving = false;
            }
        },

        async editProduct(product) {
            this.updating = product.id;
            try {
                // Fetch fresh product data
                const response = await api.getProduct(product.id);
                const freshProduct = response.data.product;
                
                this.editingProduct = freshProduct;
                this.productForm = {
                    name: freshProduct.name,
                    description: freshProduct.description || '',
                    price: freshProduct.regular_price || freshProduct.price || '',
                    image_url: freshProduct.images && freshProduct.images[0] ? freshProduct.images[0].src : '',
                    status: freshProduct.status || 'draft'
                };
            } catch (error) {
                this.showMessage('Failed to load product details: ' + (error.response?.data?.message || error.message), 'error');
            } finally {
                this.updating = null;
            }
        },

        async deleteProduct(product) {
            if (!confirm('Are you sure you want to delete this product from WooCommerce?')) return;
            
            this.deleting = product.id;
            try {
                const response = await api.deleteProduct(product.id);
                this.showMessage(response.data.message, 'success');
                await this.loadProducts();
            } catch (error) {
                this.showMessage('Failed to delete product: ' + (error.response?.data?.message || error.message), 'error');
            } finally {
                this.deleting = null;
            }
        },

        closeModal() {
            this.showCreateForm = false;
            this.editingProduct = null;
            this.productForm = {
                name: '',
                description: '',
                price: '',
                image_url: '',
                status: 'draft'
            };
        },

        showMessage(message, type = 'success') {
            this.message = message;
            this.messageType = type;
            setTimeout(() => {
                this.message = '';
            }, 5000);
        },

        handleImageError(event) {
            event.target.src = '/placeholder-image.svg';
        },

        debouncedSearch: (() => {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.loadProducts(1);
                }, 500);
            };
        })()
    }
}
</script>

<style scoped>
.products-section {
    padding: 1rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-header h2 {
    margin: 0;
    color: #333;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.filters-section {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.search-box input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 300px;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem;
    color: #666;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.empty-state small {
    display: block;
    margin-top: 0.5rem;
    color: #999;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.product-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    border-left: 4px solid #28a745;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.product-image {
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: 1rem;
}

.product-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    color: #333;
}

.product-description {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.price {
    font-weight: bold;
    color: #28a745;
    font-size: 1.1rem;
}

.status {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    text-transform: capitalize;
}

.status.publish {
    background: #d4edda;
    color: #155724;
}

.status.draft {
    background: #f8d7da;
    color: #721c24;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #545b62;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c82333;
}

.btn-outline {
    background: transparent;
    color: #007bff;
    border: 1px solid #007bff;
}

.btn-outline:hover:not(:disabled) {
    background: #007bff;
    color: white;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.page-info {
    color: #666;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #ddd;
}

.modal-header h3 {
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.product-form {
    padding: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group textarea {
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

.message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 4px;
    color: white;
    z-index: 1001;
    animation: slideIn 0.3s ease-out;
}

.message.success {
    background: #28a745;
}

.message.error {
    background: #dc3545;
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

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: stretch;
    }

    .header-actions {
        justify-content: center;
    }

    .filters-section {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box input {
        width: 100%;
    }

    .products-grid {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .product-actions {
        justify-content: center;
    }
}
</style>
