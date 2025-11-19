<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ===================================
            // THÔNG TIN ĐĂNG NHẬP
            // ===================================
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // ===================================
            // THÔNG TIN CÁ NHÂN (E-E-A-T + REVIEW)
            // ===================================
            $table->string('phone', 20)->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('telegram', 50)->nullable();
            $table->string('zalo', 50)->nullable();
            $table->text('bio')->nullable();
            $table->date('birthday')->nullable();

            // ===================================
            // QUYỀN & TRẠNG THÁI
            // ===================================
            $table->string('role')->default('customer');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_banned')->default(false);
            $table->timestamp('banned_until')->nullable();

            // ===================================
            // AFFILIATE & ĐIỂM THƯỞNG
            // ===================================
            $table->string('referral_code', 20)->unique()->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->unsignedBigInteger('affiliate_earnings')->default(0);
            $table->unsignedBigInteger('points')->default(0);

            // ===================================
            // SỐ DƯ & LỊCH SỬ NẠP
            // ===================================
            $table->unsignedBigInteger('balance')->default(0);         // Số dư tài khoản
            $table->unsignedBigInteger('total_deposited')->default(0); // Tổng tiền đã nạp

            // ===================================
            // KYC & ANTI-SPAM
            // ===================================
            $table->boolean('kyc_verified')->default(false);
            $table->string('kyc_id_card')->nullable();
            $table->string('kyc_selfie')->nullable();
            $table->timestamp('kyc_verified_at')->nullable();

            // ===================================
            // THỐNG KÊ E-E-A-T
            // ===================================
            $table->unsignedBigInteger('total_orders')->default(0);
            $table->unsignedBigInteger('total_spent')->default(0);
            $table->unsignedInteger('successful_orders')->default(0);
            $table->dateTime('last_order_at')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            // ===================================
            // BẢO MẬT & CHỐNG SPAM LOGIN
            // ===================================
            $table->unsignedTinyInteger('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            // Anti-bot & spam đăng ký
            $table->string('register_ip', 45)->nullable();
            $table->string('register_user_agent')->nullable();
            $table->string('verification_token', 100)->nullable()->unique();
            $table->timestamp('verification_sent_at')->nullable();

            // Google reCAPTCHA v3 score (0.0 → 1.0)
            $table->float('recaptcha_score', 3, 2)->nullable();

            // Fingerprint thiết bị (chống tạo nhiều acc)
            $table->string('device_fingerprint', 100)->nullable();

            // ===================================
            // SEO & SOCIAL LOGIN
            // ===================================
            $table->json('social_providers')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('facebook_id')->nullable()->unique();

            // ===================================
            // NHẬN THÔNG BÁO
            // ===================================
            $table->boolean('notify_order')->default(true);
            $table->boolean('notify_promotion')->default(true);
            $table->boolean('notify_account')->default(true);

            $table->rememberToken();
            $table->timestamps();

            // ===================================
            // INDEX TỐI ƯU TÌM KIẾM & BẢO MẬT
            // ===================================
            $table->index(['role', 'is_active']);
            $table->index('referred_by');
            $table->index('total_orders');
            $table->index('balance');
            $table->index('created_at');
            $table->index('last_login_at');
            $table->index('kyc_verified');
            $table->index(['register_ip', 'created_at']);
            $table->index('device_fingerprint');
            $table->index('locked_until');
        });

        // Tạo bảng referral riêng
        Schema::create('user_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('commission')->default(0);
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'referred_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_referrals');
        Schema::dropIfExists('users');
    }
};
