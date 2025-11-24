<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->get('category');
        $query = Product::with('category')->where('is_active', true);

        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        $products = $query->latest()->paginate(20);

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories', 'categorySlug'));
    }

    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->first();
        
        if (!$product || !$product->is_active) {
            abort(404);
        }

        $product->load('category');
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(6)
            ->get();

        // Get active payment gateways
        $paymentGateways = PaymentGateway::getActive();

        return view('products.show', compact('product', 'relatedProducts', 'paymentGateways'));
    }
}


