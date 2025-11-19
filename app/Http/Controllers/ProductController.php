<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function show($slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Tăng view thật (chống spam)
        $product->increaseView();

        // Sản phẩm liên quan
        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(8)
            ->get();

        // SEO
        $title = $product->meta_title ?? $product->name . ' - Giá Rẻ Nhất 2025';
        $description = $product->meta_description ?? Str::limit(strip_tags($product->short_description), 160);

        return view('products.show', compact('product', 'related', 'title', 'description'));
    }
}