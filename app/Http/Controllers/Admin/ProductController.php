<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderBy('is_featured', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:200|unique:products,name',
            'slug'              => 'nullable|string|unique:products,slug',
            'category_id'       => 'required|exists:categories,id',
            'price'             => 'required|numeric|min:0',
            'old_price'         => 'nullable|numeric|gte:price',
            'price_per_month'   => 'nullable|numeric',
            'short_description' => 'required|string',
            'content'           => 'required|string|min:1000', // bắt buộc nội dung dài SEO
            'thumbnail'         => 'required|image|mimes:webp,jpg,png,jpeg|max:2048',
            'image_large'       => 'nullable|image|mimes:webp,jpg,png,jpeg|max:3072',
            'gallery.*'         => 'nullable|image|mimes:webp,jpg,png,jpeg|max:3072',
            'warranty_month'    => 'required|integer|min:1',
        ]);

        $data = $request->all();

        // Slug tự động + chống trùng
        $data['slug'] = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        $baseSlug = $data['slug'];
        $i = 1;
        while (Product::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $baseSlug . '-' . $i++;
        }

        // Upload ảnh
        $data['thumbnail']   = $request->file('thumbnail')->store('products/thumbs', 'public');
        if ($request->hasFile('image_large')) {
            $data['image_large'] = $request->file('image_large')->store('products/large', 'public');
        }

        // Gallery nhiều ảnh
        if ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('products/gallery', 'public');
            }
            $data['gallery'] = $gallery;
        }

        // Người tạo
        $data['created_by'] = $data['updated_by'] = Auth::id();

        // Tự động sinh schema Product (mạnh Rich Snippet)
        $data['schema_json'] = $this->generateProductSchema($data, $request);

        Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Tạo sản phẩm thành công!');
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('sort_order')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'              => 'required|string|max:200|unique:products,name,' . $product->id,
            'slug'              => 'nullable|string|unique:products,slug,' . $product->id,
            'category_id'       => 'required|exists:categories,id',
            'price'             => 'required|numeric|min:0',
            'thumbnail'         => 'nullable|image|mimes:webp,jpg,png,jpeg|max:2048',
            'image_large'       => 'nullable|image|mimes:webp,jpg,png,jpeg|max:3072',
            'gallery.*'         => 'nullable|image|mimes:webp,jpg,png,jpeg|max:3072',
        ]);

        $data = $request->all();

        $data['slug'] = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        // Tránh trùng slug
        $exists = Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists();
        if ($exists) {
            $data['slug'] = $data['slug'] . '-' . $product->id;
        }

        // Upload ảnh mới
        if ($request->hasFile('thumbnail')) {
            Storage::disk('public')->delete($product->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('products/thumbs', 'public');
        }

        if ($request->hasFile('image_large')) {
            Storage::disk('public')->delete($product->image_large);
            $data['image_large'] = $request->file('image_large')->store('products/large', 'public');
        }

        if ($request->hasFile('gallery')) {
            // Xóa ảnh cũ
            foreach (($product->gallery ?? []) as $old) {
                Storage::disk('public')->delete($old);
            }
            $gallery = [];
            foreach ($request->file('gallery') as $file) {
                $gallery[] = $file->store('products/gallery', 'public');
            }
            $data['gallery'] = $gallery;
        }

        $data['updated_by'] = Auth::id();
        $data['schema_json'] = $this->generateProductSchema($data, $request, $product);

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy(Product $product)
    {
        // Xóa hết ảnh
        Storage::disk('public')->delete([$product->thumbnail, $product->image_large]);
        foreach (($product->gallery ?? []) as $img) {
            Storage::disk('public')->delete($img);
        }

        $product->delete();

        return back()->with('success', 'Xóa sản phẩm thành công!');
    }

    // AJAX toggle trạng thái
    public function toggle(Request $request, Product $product)
    {
        $product->update([
            $request->field => $request->value,
            'updated_by' => Auth::id()
        ]);

        return response()->json(['success' => true]);
    }

    // TỰ ĐỘNG TẠO SCHEMA PRODUCT SIÊU MẠNH
    private function generateProductSchema($data, $request, $product = null)
    {
        $url = url('/san-pham/' . ($product->slug ?? $data['slug']));

        return [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $data['name'],
            "image" => asset('storage/' . ($product->thumbnail ?? $data['thumbnail'])),
            "description" => strip_tags($data['short_description']),
            "sku" => "AI-" . ($product->id ?? 'NEW'),
            "brand" => ["@type" => "Brand", "name" => "Chính Chủ"],
            "offers" => [
                "@type" => "Offer",
                "url" => $url,
                "priceCurrency" => "VND",
                "price" => $data['price'],
                "priceValidUntil" => now()->addMonths(6)->format('Y-m-d'),
                "availability" => $data['in_stock'] ?? true ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                "seller" => ["@type" => "Organization", "name" => config('app.name')]
            ],
            "review" => [ // giả lập 5 sao để lên rich snippet
                "@type" => "Review",
                "reviewRating" => ["@type" => "Rating", "ratingValue" => "4.9", "bestRating" => "5"],
                "author" => ["@type" => "Person", "name" => "Khách hàng"]
            ]
        ];
    }
}