<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ===================================
            // THÔNG TIN SẢN PHẨM CHÍNH
            // ===================================
            $table->string('name', 200);                                      // Tên hiển thị
            $table->string('slug', 220)->unique();                           // URL cực sạch
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            $table->decimal('price', 15, 2);                                  // Giá bán hiện tại
            $table->decimal('old_price', 15, 2)->nullable();                  // Giá cũ (giảm giá)
            $table->decimal('price_per_month', 10, 2)->nullable();            // Giá/tháng (hiển thị nổi bật)

            $table->text('short_description');                                // Mô tả ngắn dưới tên
            $table->longText('content');                                      // Nội dung chi tiết 2000–5000 từ (bắt buộc SEO)

            // ===================================
            // THÔNG TIN TÀI KHOẢN AI
            // ===================================
            $table->string('account_type', 100)->nullable();     // ChatGPT Plus, Claude Pro, Grok 4, v.v.
            $table->integer('warranty_month')->default(1);       // Bảo hành mấy tháng
            $table->boolean('is_lifetime')->default(false);      // Trọn đời
            $table->boolean('is_private')->default(true);        // Tài khoản riêng (không share)
            $table->boolean('has_invoice')->default(true);       // Có hóa đơn OpenAI/Google/Anthropic
            $table->integer('max_devices')->default(5);          // Đăng nhập tối đa mấy thiết bị

            // ===================================
            // HÌNH ẢNH & MEDIA
            // ===================================
            $table->string('thumbnail')->nullable();             // Ảnh nhỏ danh sách
            $table->string('image_large')->nullable();           // Ảnh chi tiết
            $table->json('gallery')->nullable();                // Nhiều ảnh minh họa (screenshot tài khoản, hóa đơn...)

            // ===================================
            // TRẠNG THÁI & HIỂN THỊ
            // ===================================
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();   // Nổi bật homepage
            $table->boolean('is_hot')->default(false);                  // Hot deal
            $table->boolean('in_stock')->default(true)->index();
            $table->integer('stock_quantity')->default(999);           // Số lượng còn lại (hiển thị khan hiếm)
            $table->unsignedInteger('sort_order')->default(0);

            // ===================================
            // SEO SIÊU MẠNH 2025
            // ===================================
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            
            // Open Graph / Social
            $table->string('og_title', 120)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('og_image')->nullable();

            $table->string('robots', 50)->default('index, follow');

            // Schema JSON-LD tự động (Product + Offer + Review)
            $table->json('schema_json')->nullable();

            // ===================================
            // THỐNG KÊ E-E-A-T (GOOGLE RẤT THÍCH)
            // ===================================
            $table->unsignedBigInteger('view_count')->default(0)->index();
            $table->unsignedBigInteger('sale_count')->default(0)->index();
            $table->unsignedBigInteger('click_count')->default(0);     // Click nút Mua ngay

            // Ai tạo / ai sửa
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // ===================================
            // INDEX TỐI ƯU TÌM KIẾM & TỐC ĐỘ
            // ===================================
            $table->index(['is_active', 'is_featured', 'in_stock']);
            $table->index('category_id');
            $table->index('price');
            $table->index('warranty_month');
            $table->index('created_at');
            $table->fullText(['name', 'short_description', 'content']); // Tìm kiếm fulltext siêu nhanh
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};