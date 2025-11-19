<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::where('parent_id', null)->active()->orderBy('sort_order')->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:categories,name',
            'slug' => 'nullable|string|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:150',
            'banner' => 'nullable|image|mimes:webp,jpg,png|max:2048',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:320',
        ]);

        $data = $request->all();

        // Tự động tạo slug
        $data['slug'] = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);
        
        // Kiểm tra trùng slug
        $baseSlug = $data['slug'];
        $count = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $baseSlug . '-' . $count++;
        }

        // Upload banner
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('categories', 'public');
        }

        // Người tạo / sửa
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Tạo danh mục thành công!');
    }

    public function edit(Category $category)
    {
        $parents = Category::where('parent_id', null)
            ->where('id', '!=', $category->id)
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'banner' => 'nullable|image|mimes:webp,jpg,png|max:2048',
        ]);

        $data = $request->all();

        $data['slug'] = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->name);

        // Tránh trùng slug (trừ chính nó)
        $query = Category::where('slug', $data['slug'])->where('id', '!=', $category->id);
        if ($query->exists()) {
            $data['slug'] = $data['slug'] . '-' . $category->id;
        }

        if ($request->hasFile('banner')) {
            // Xóa file cũ
            if ($category->banner) {
                Storage::disk('public')->delete($category->banner);
            }
            $data['banner'] = $request->file('banner')->store('categories', 'public');
        }

        $data['updated_by'] = Auth::id();

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() || $category->products()->count()) {
            return back()->with('error', 'Không thể xóa danh mục đang có sản phẩm hoặc danh mục con!');
        }

        if ($category->banner) {
            Storage::disk('public')->delete($category->banner);
        }

        $category->delete();

        return back()->with('success', 'Xóa danh mục thành công!');
    }

    // AJAX thay đổi trạng thái
    public function toggle(Request $request, Category $category)
    {
        $category->update([
            $request->field => $request->value,
            'updated_by' => Auth::id()
        ]);

        return response()->json(['success' => true]);
    }
}