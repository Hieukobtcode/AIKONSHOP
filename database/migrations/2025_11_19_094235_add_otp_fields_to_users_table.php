<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp_code', 6)->nullable();           // mã 6 số
            $table->timestamp('otp_expires_at')->nullable();     // hết hạn
            $table->timestamp('otp_sent_at')->nullable();        // thời gian gửi
            $table->boolean('email_verified_at')->nullable()->change(); // đổi sang datetime nullable
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_code', 'otp_expires_at', 'otp_sent_at']);
        });
    }
};
