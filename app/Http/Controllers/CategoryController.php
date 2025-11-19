<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function show($slug)
    {
        $category = Category::with(['children' => fn($q) => $q->active()->orderBy('sort_order')])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Tăng view count (dùng cache để tránh spam)
        Cache::remember("category_view_{$category->id}", now()->addHours(1), function () use ($category) {
            $category->increment('view_count');
        });

        // Lấy sản phẩm thuộc danh mục + danh mục con
        $productQuery = Product::active()
            ->where(function ($q) use ($category) {
                $q->where('category_id', $category->id)
                  ->orWhereIn('category_id', $category->children->pluck('id'));
            })
            ->with(['category'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('id', 'desc');

        $products = $productQuery->paginate(24);

        // SEO Meta động
        $title = $category->meta_title ?? $category->name . ' - Giá Rẻ, Chính Chủ, Bảo Hành Dài Hạn';
        $description = $category->meta_description ?? Str::limit(strip_tags($category->content), 180);

        return view('categories.show', compact(
            'category',
            'products',
            'title',
            'description'
        ));
    }

    // Optional: Trang danh sách tất cả danh mục (sitemap con người)
    public function index()
    {
        $categories = Category::withCount('products')
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('categories.index', compact('categories'));
    }
}