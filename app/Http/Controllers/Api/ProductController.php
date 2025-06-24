<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProductController extends Controller
{
    protected $wooCommerceService;

    public function __construct(WooCommerceService $wooCommerceService)
    {
        $this->wooCommerceService = $wooCommerceService;
    }

    /**
     * Get all products from WooCommerce
     */
    public function index(Request $request)
    {
        try {
            $params = [];
            
            if ($request->has('per_page')) {
                $params['per_page'] = $request->per_page;
            }
            
            if ($request->has('page')) {
                $params['page'] = $request->page;
            }
            
            if ($request->has('search')) {
                $params['search'] = $request->search;
            }

            $products = $this->wooCommerceService->getProducts($params);

            return response()->json([
                'success' => true,
                'products' => $products,
                'message' => 'WooCommerce products fetched successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch WooCommerce products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new product directly in WooCommerce
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'image_url' => 'nullable|url',
                'status' => 'in:draft,publish'
            ]);

            $productData = [
                'name' => $request->name,
                'description' => $request->description,
                'regular_price' => (string) $request->price,
                'status' => $request->status ?? 'draft',
                'type' => 'simple'
            ];

            if ($request->image_url) {
                $productData['images'] = [
                    [
                        'src' => $request->image_url
                    ]
                ];
            }

            $wcProduct = $this->wooCommerceService->createProduct($productData);

            // Optionally store a reference in local database for tracking
            Product::create([
                'user_id' => Auth::id(),
                'name' => $wcProduct->name,
                'description' => $wcProduct->description,
                'price' => $wcProduct->regular_price ?: $wcProduct->price,
                'image_url' => isset($wcProduct->images[0]) ? $wcProduct->images[0]->src : null,
                'status' => $wcProduct->status === 'publish' ? 'published' : 'draft',
                'wc_product_id' => $wcProduct->id
            ]);

            return response()->json([
                'success' => true,
                'product' => $wcProduct,
                'message' => 'Product created successfully in WooCommerce'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product in WooCommerce: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product in WooCommerce
     */
    public function update(Request $request, $productId)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'image_url' => 'nullable|url',
                'status' => 'in:draft,publish'
            ]);

            $productData = [
                'name' => $request->name,
                'description' => $request->description,
                'regular_price' => (string) $request->price,
                'status' => $request->status ?? 'draft'
            ];

            if ($request->image_url) {
                $productData['images'] = [
                    [
                        'src' => $request->image_url
                    ]
                ];
            }

            $wcProduct = $this->wooCommerceService->updateProduct($productId, $productData);

            // Update local reference if exists
            $localProduct = Product::where('wc_product_id', $productId)->first();
            if ($localProduct) {
                $localProduct->update([
                    'name' => $wcProduct->name,
                    'description' => $wcProduct->description,
                    'price' => $wcProduct->regular_price ?: $wcProduct->price,
                    'image_url' => isset($wcProduct->images[0]) ? $wcProduct->images[0]->src : null,
                    'status' => $wcProduct->status === 'publish' ? 'published' : 'draft'
                ]);
            }

            return response()->json([
                'success' => true,
                'product' => $wcProduct,
                'message' => 'Product updated successfully in WooCommerce'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product in WooCommerce: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product from WooCommerce
     */
    public function destroy($productId)
    {
        try {
            $this->wooCommerceService->deleteProduct($productId);

            // Remove local reference if exists
            Product::where('wc_product_id', $productId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully from WooCommerce'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product from WooCommerce: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single product from WooCommerce
     */
    public function show($productId)
    {
        try {
            $product = $this->wooCommerceService->getProduct($productId);

            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Product fetched successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage()
            ], 500);
        }
    }
}
