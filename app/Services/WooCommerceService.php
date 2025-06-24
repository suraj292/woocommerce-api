<?php

namespace App\Services;

use Automattic\WooCommerce\Client;
use Exception;

class WooCommerceService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.woocommerce.url'),
            config('services.woocommerce.consumer_key'),
            config('services.woocommerce.consumer_secret'),
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'timeout' => 30
            ]
        );
    }

    /**
     * Get all products from WooCommerce
     */
    public function getProducts($params = [])
    {
        try {
            $defaultParams = [
                'per_page' => 50,
                'status' => 'publish'
            ];
            
            $params = array_merge($defaultParams, $params);
            
            $response = $this->client->get('products', $params);
            return $response;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch products from WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Get a single product from WooCommerce
     */
    public function getProduct($productId)
    {
        try {
            return $this->client->get("products/{$productId}");
        } catch (Exception $e) {
            throw new Exception('Failed to fetch product from WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Create a product in WooCommerce
     */
    public function createProduct($productData)
    {
        try {
            return $this->client->post('products', $productData);
        } catch (Exception $e) {
            throw new Exception('Failed to create product in WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Update a product in WooCommerce
     */
    public function updateProduct($productId, $productData)
    {
        try {
            return $this->client->put("products/{$productId}", $productData);
        } catch (Exception $e) {
            throw new Exception('Failed to update product in WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Delete a product in WooCommerce
     */
    public function deleteProduct($productId)
    {
        try {
            return $this->client->delete("products/{$productId}", ['force' => true]);
        } catch (Exception $e) {
            throw new Exception('Failed to delete product from WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Sync local product with WooCommerce
     */
    public function syncProductToWooCommerce($product)
    {
        $productData = [
            'name' => $product->name,
            'description' => $product->description,
            'regular_price' => (string) $product->price,
            'status' => $product->status === 'published' ? 'publish' : 'draft'
        ];

        if ($product->image_url) {
            $productData['images'] = [
                [
                    'src' => $product->image_url
                ]
            ];
        }

        if ($product->wc_product_id) {
            // Update existing product
            $response = $this->updateProduct($product->wc_product_id, $productData);
        } else {
            // Create new product
            $response = $this->createProduct($productData);
            // Update local product with WooCommerce ID
            $product->update(['wc_product_id' => $response->id]);
        }

        return $response;
    }
}
