# WooCommerce API Integration with Laravel

A Laravel-based API service that integrates with WooCommerce to provide a secure and scalable solution for managing WooCommerce store data through RESTful APIs.

## 🚀 Project Overview

This project serves as a middleware API layer between your applications and WooCommerce stores. It provides authenticated access to WooCommerce products, orders, and other store data while maintaining security through Laravel Sanctum token-based authentication.

### Key Features

- **WooCommerce Integration**: Direct integration with WooCommerce REST API v3
- **Secure Authentication**: Token-based authentication using Laravel Sanctum
- **Product Management**: Fetch, create, update, and delete WooCommerce products
- **User Management**: User registration, login, and profile management
- **Database Storage**: Local database storage for users and cached data
- **Modern API**: RESTful API design with JSON responses

## 🛠️ Technology Stack

- **Framework**: Laravel 10.x
- **PHP**: 8.1+
- **Authentication**: Laravel Sanctum (API Token Authentication)
- **WooCommerce**: Automattic WooCommerce PHP Client v3.1
- **Database**: MySQL/SQLite
- **HTTP Client**: Guzzle HTTP
- **Testing**: PHPUnit

## 📋 Prerequisites

Before setting up this project, ensure you have:

1. **PHP 8.1 or higher**
2. **Composer** (PHP dependency manager)
3. **MySQL/SQLite** database
4. **WooCommerce Store** with API access enabled
5. **WooCommerce API Credentials** (Consumer Key & Secret)

## ⚙️ Installation & Setup

### 1. Clone the Repository
```bash
git clone <your-repository-url>
cd woocommerce-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment Variables

Edit your `.env` file with the following configurations:

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=woocommerce_api
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password
```

#### WooCommerce API Configuration
```env
WOOCOMMERCE_URL=https://your-store.com
WOOCOMMERCE_CONSUMER_KEY=ck_your_consumer_key_here
WOOCOMMERCE_CONSUMER_SECRET=cs_your_consumer_secret_here
```

#### Application Settings
```env
APP_NAME="WooCommerce API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### 5. Database Setup
```bash
php artisan migrate
```

### 6. Start the Development Server
```bash
php artisan serve
```

Your API will be available at `http://localhost:8000`

## 🔐 Authentication System

### API Authentication with Laravel Sanctum

This project uses **Laravel Sanctum** for API authentication, providing a simple token-based authentication system.

#### How It Works:
1. **User Registration/Login**: Users register or login through API endpoints
2. **Token Generation**: Upon successful authentication, a personal access token is generated
3. **Token Usage**: Include the token in the `Authorization` header for subsequent requests
4. **Token Revocation**: Tokens can be revoked during logout

#### Authentication Flow:
```
1. POST /api/register or /api/login
2. Receive authentication token
3. Include token in headers: Authorization: Bearer {token}
4. Access protected endpoints
```

## 🔌 WooCommerce Integration

### WooCommerce REST API v3

The project integrates with WooCommerce using the official **Automattic WooCommerce PHP Client**.

#### Setup WooCommerce API Access:

1. **Enable REST API**: In your WooCommerce admin, go to WooCommerce → Settings → Advanced → REST API
2. **Create API Keys**: 
   - Click "Add Key"
   - Set Description: "Laravel API Integration"
   - Set User: Your admin user
   - Set Permissions: "Read/Write"
   - Copy the Consumer Key and Consumer Secret

3. **Configure Permissions**: Ensure your WooCommerce user has appropriate permissions

### Supported WooCommerce Operations:
- ✅ **Products**: List, view, create, update, delete
- ✅ **Product Categories**: List and manage
- 🔄 **Orders**: View and manage (extensible)
- 🔄 **Customers**: Manage customer data (extensible)

## 📚 API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/register` | Register new user | No |
| POST | `/api/login` | User login | No |
| GET | `/api/user` | Get authenticated user | Yes |
| POST | `/api/logout` | User logout | Yes |

### Product Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/products` | List WooCommerce products | Yes |
| GET | `/api/products/{id}` | Get single product | Yes |
| POST | `/api/products` | Create new product | Yes |
| PUT | `/api/products/{id}` | Update product | Yes |
| DELETE | `/api/products/{id}` | Delete product | Yes |

### Example API Usage

#### 1. User Registration
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'
```

#### 2. User Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password"
  }'
```

#### 3. Get Products (with authentication)
```bash
curl -X GET http://localhost:8000/api/products \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json"
```

## 🛣️ Complete Route List

### API Routes (Prefix: `/api`)

All API routes are located in `routes/api.php` and are prefixed with `/api`.

#### 🔓 Public Routes (No Authentication Required)

| Method | Route | Controller@Method | Description |
|--------|-------|-------------------|-------------|
| POST | `/api/register` | `AuthController@register` | Register a new user account |
| POST | `/api/login` | `AuthController@login` | Authenticate user and get API token |

**Example Request Bodies:**

**Registration:**
```json
{
  "name": "John Doe",
  "email": "john@example.com", 
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Login:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### 🔐 Protected Routes (Authentication Required)

All protected routes require the `Authorization: Bearer {token}` header.

##### Authentication Management

| Method | Route | Controller@Method | Description |
|--------|-------|-------------------|-------------|
| GET | `/api/user` | `AuthController@user` | Get current authenticated user details |
| POST | `/api/logout` | `AuthController@logout` | Logout and revoke current API token |

##### Product Management (WooCommerce Integration)

| Method | Route | Controller@Method | Description |
|--------|-------|-------------------|-------------|
| GET | `/api/products` | `ProductController@index` | List all WooCommerce products |
| POST | `/api/products` | `ProductController@store` | Create new product in WooCommerce |
| GET | `/api/products/{productId}` | `ProductController@show` | Get single WooCommerce product by ID |
| PUT | `/api/products/{productId}` | `ProductController@update` | Update WooCommerce product by ID |
| DELETE | `/api/products/{productId}` | `ProductController@destroy` | Delete WooCommerce product by ID |

**Product Endpoints Query Parameters:**

- **GET `/api/products`** supports query parameters:
  - `per_page` (default: 50) - Number of products per page
  - `page` - Page number for pagination
  - `status` (default: 'publish') - Product status filter
  - `category` - Filter by category ID
  - `search` - Search products by name/description

**Example Product Creation:**
```json
{
  "name": "Sample Product",
  "type": "simple",
  "regular_price": "29.99",
  "description": "Product description here",
  "short_description": "Short description",
  "categories": [
    {"id": 1}
  ],
  "images": [
    {"src": "https://example.com/image.jpg"}
  ]
}
```

### Web Routes (Browser Access)

Web routes are located in `routes/web.php` and are accessible via browser.

| Method | Route | View/Action | Description |
|--------|-------|-------------|-------------|
| GET | `/` | `welcome.blade.php` | Application welcome page |
| GET | `/vue-demo` | `vue-demo.blade.php` | Vue.js demonstration page |
| GET | `/auth` | `auth.blade.php` | Authentication demo page |

### Route Testing Commands

You can list all available routes using Laravel's artisan command:

```bash
# List all routes
php artisan route:list

# List only API routes
php artisan route:list --path=api

# List routes with specific methods
php artisan route:list --method=GET
```

### API Response Formats

#### Success Response Format
```json
{
  "success": true,
  "data": {
    // Response data here
  },
  "message": "Operation successful"
}
```

#### Error Response Format
```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    // Validation errors (if any)
  }
}
```

#### Authentication Token Response
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|laravel_sanctum_token_here"
  },
  "message": "Login successful"
}
```

### HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 500 | Internal Server Error - Server error |

## 🏗️ Project Structure

```
app/
├── Http/Controllers/Api/
│   ├── AuthController.php          # Authentication logic
│   └── ProductController.php       # Product management
├── Services/
│   └── WooCommerceService.php      # WooCommerce API integration
└── Models/
    ├── User.php                    # User model
    └── Product.php                 # Product model

config/
└── services.php                   # WooCommerce configuration

routes/
└── api.php                       # API routes definition
```

## 🔧 Key Components

### WooCommerceService
Located at `app/Services/WooCommerceService.php`, this service handles all WooCommerce API interactions:
- Product fetching and management
- Order processing
- Error handling and logging
- API response formatting

### Authentication Controllers
- **AuthController**: Handles user registration, login, logout
- **Sanctum Integration**: Manages API tokens

### Product Controllers
- **ProductController**: Manages product CRUD operations
- **WooCommerce Sync**: Synchronizes with WooCommerce store

## 🚀 Deployment

### Production Environment Setup

1. **Environment Configuration**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Database Migration**:
   ```bash
   php artisan migrate --force
   ```

3. **Optimize Application**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Set Proper Permissions**:
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

## 🔒 Security Considerations

- **API Rate Limiting**: Implement rate limiting for API endpoints
- **CORS Configuration**: Configure CORS for cross-origin requests
- **HTTPS**: Always use HTTPS in production
- **Token Expiration**: Configure appropriate token expiration times
- **Input Validation**: All inputs are validated before processing

## 📖 Documentation

### WooCommerce API Documentation
- [WooCommerce REST API Documentation](https://woocommerce.github.io/woocommerce-rest-api-docs/)

### Laravel Documentation
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Commit your changes: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature/new-feature`
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
