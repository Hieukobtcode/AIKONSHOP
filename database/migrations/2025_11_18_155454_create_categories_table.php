<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // Phân cấp danh mục (SEO Silo)
            $table->unsignedBigInteger('parent_id')->nullable()->index();

            // Thông tin cơ bản
            $table->string('name', 150);                   // Tên hiển thị
            $table->string('breadcrumb_title', 150)->nullable(); // Breadcrumb riêng
            $table->string('slug', 180)->unique();         // URL thân thiện
            
            $table->text('description')->nullable();        // Mô tả ngắn
            $table->longText('content')->nullable();        // Nội dung SEO chính

            // UI – Banner / Icon
            $table->string('icon', 150)->nullable();
            $table->string('banner')->nullable();           // Banner 1920x600

            // Cài đặt hiển thị
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();

            // SEO Meta
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            // Social - OpenGraph
            $table->string('og_title', 120)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('og_image')->nullable();

            // Robots: index, noindex, follow...
            $table->string('robots', 50)->default('index, follow');

            // JSON-LD Schema (Rich Snippet)
            $table->json('schema_json')->nullable();

            // Thống kê tăng E-E-A-T
            $table->unsignedBigInteger('product_count')->default(0)->index();
            $table->unsignedBigInteger('view_count')->default(0)->index();
            $table->unsignedBigInteger('sale_count')->default(0)->index();

            // Ai viết / ai cập nhật – tăng độ tin cậy nội dung
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();

            $table->timestamps();

            // Index bổ sung
            $table->index('name');
            $table->index(['is_active', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
