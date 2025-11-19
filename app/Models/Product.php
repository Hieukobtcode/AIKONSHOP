<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
class Product extends Model
{
    protected $fillable = [
        'name','slug','category_id','price','old_price','price_per_month',
        'short_description','content','account_type','warranty_month',
        'is_lifetime','is_private','has_invoice','max_devices',
        'thumbnail','image_large','gallery','is_active','is_featured',
        'is_hot','in_stock','stock_quantity','sort_order','meta_title',
        'meta_description','meta_keywords','canonical_url','og_title',
        'og_description','og_image','robots','schema_json','created_by','updated_by'
    ];

    protected $casts = [
        'gallery' => 'array',
        'schema_json' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_hot' => 'boolean',
        'in_stock' => 'boolean',
        'is_lifetime' => 'boolean',
        'is_private' => 'boolean',
        'has_invoice' => 'boolean',
    ];

    // Quan hệ
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Tự động tạo slug
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Accessor URL
    public function getUrlAttribute()
    {
        return url('/san-pham/' . $this->slug);
    }

    // Scope
    public function scopeActive($q)
    {
        return $q->where('is_active', true)->where('in_stock', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    // Tăng view (có cache chống spam)
    public function increaseView()
    {
        Cache::remember("product_view_{$this->id}", now()->addMinutes(30), function () {
            $this->increment('view_count');
        });
    }
}