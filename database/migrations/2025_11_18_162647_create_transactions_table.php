<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('type');                    // deposit, withdraw, payment, refund, affiliate
            $table->unsignedBigInteger('amount');       // số tiền giao dịch
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            
            $table->string('gateway')->nullable();      // momo, vnpay, banking, usdt, manual
            $table->string('transaction_id')->nullable(); // mã GD từ cổng
            $table->text('note')->nullable();           // ghi chú admin
            
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])
                  ->default('pending');
            
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // admin duyệt tay
            
            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};