<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'parent_id',
        'name',
        'breadcrumb_title',
        'slug',
        'description',
        'content',
        'icon',
        'banner',
        'sort_order',
        'is_active',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'robots',
        'schema_json',
        'product_count',
        'view_count',
        'sale_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // -----------------------------
    // QUAN HỆ DANH MỤC CHA – CON
    // -----------------------------
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // -----------------------------
    // MUTATOR: Tự tạo slug nếu không nhập
    // -----------------------------
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;

        if (! isset($this->attributes['slug']) || empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value, '-');
        }
    }

    // -----------------------------
    // SCOPE TÌM KIẾM
    // -----------------------------
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    public function scopeTop($q)
    {
        return $q->orderBy('sort_order', 'asc');
    }

    // -----------------------------
    // ACCESSOR: FULL URL SEO
    // -----------------------------
    public function getUrlAttribute()
    {
        return url('/danh-muc/' . $this->slug);
    }
}
