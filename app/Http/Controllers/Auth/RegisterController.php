<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // === BẢO MẬT CHẶT NHẤT ===
        if ($request->filled('website_url')) return back()->with('error', 'Lỗi hệ thống.');
        if ($request->has('form_submitted_at') && now()->timestamp - $request->integer('form_submitted_at') < 4) {
            return back()->with('error', 'Gửi quá nhanh!');
        }

        $rateKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 4)) {
            return back()->withErrors(['email' => 'Quá nhiều lượt đăng ký. Vui lòng chờ.']);
        }

        // hCaptcha
        $captcha = $request->input('h-captcha-response');
        if (!$captcha) return back()->withErrors(['captcha' => 'Vui lòng xác minh captcha.']);
        $verify = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret' => config('services.hcaptcha.secret'),
            'response' => $captcha,
            'remoteip' => $request->ip(),
        ])->json();
        if (!$verify['success']) {
            RateLimiter::hit($rateKey, 300);
            return back()->withErrors(['captcha' => 'Captcha không hợp lệ.']);
        }

        // Device Fingerprint
        $fingerprint = $request->input('device_fingerprint');
        if (!$fingerprint || User::where('device_fingerprint', $fingerprint)->exists()) {
            return back()->withErrors(['email' => 'Thiết bị đã được dùng để đăng ký.']);
        }

        // Validate
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'name.required'     => 'Vui lòng nhập họ và tên.',
            'name.max'          => 'Họ và tên không được vượt quá 100 ký tự.',
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Địa chỉ email không hợp lệ.',
            'email.unique'      => 'Email này đã được đăng ký.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min'      => 'Mật khẩu phải tối thiểu :min ký tự.',
        ]);


        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'telegram'            => $request->telegram,
            'register_ip'         => $request->ip(),
            'register_user_agent' => $request->userAgent(),
            'device_fingerprint'  => $fingerprint,
            'is_active'           => true,
        ]);

        RateLimiter::clear($rateKey);

        return redirect()->route('login')
            ->with('success', "Đăng ký tài khoản thành công");
    }
}
