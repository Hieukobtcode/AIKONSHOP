<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Honeypot – bot chết ngay từ đầu
        if ($request->filled('my_website') || $request->has('my_website')) {
            Log::warning('Login honeypot triggered', ['ip' => $request->ip()]);
            throw ValidationException::withMessages(['email' => 'Lỗi hệ thống.']);
        }

        // 2. Submit quá nhanh (< 3s)
        if ($request->has('form_submitted_at') && now()->timestamp - $request->integer('form_submitted_at') < 3) {
            throw ValidationException::withMessages(['email' => 'Gửi quá nhanh!']);
        }

        // 3. Rate Limiter toàn site + IP
        $ipKey = 'login:ip:'.$request->ip();
        $globalKey = 'login:global';

        if (RateLimiter::tooManyAttempts($ipKey, 8) || RateLimiter::tooManyAttempts($globalKey, 50)) {
            $seconds = RateLimiter::availableIn($ipKey);
            throw ValidationException::withMessages([
                'email' => "Quá nhiều lần đăng nhập. Vui lòng chờ {$seconds} giây."
            ]);
        }

        // 4. hCaptcha bắt buộc
        $captcha = $request->input('h-captcha-response');
        if (!$captcha) {
            RateLimiter::hit($ipKey);
            throw ValidationException::withMessages(['captcha' => 'Vui lòng xác minh bạn không phải robot.']);
        }

        $verify = Http::asForm()->post('https://hcaptcha.com/siteverify', [
            'secret'   => config('services.hcaptcha.secret'),
            'response' => $captcha,
            'remoteip' => $request->ip(),
        ])->json();

        if (!$verify['success']) {
            RateLimiter::hit($ipKey, 300);
            throw ValidationException::withMessages(['captcha' => 'Xác minh captcha thất bại.']);
        }

        // 5. Device Fingerprint (chống 1 máy đăng nhập 1000 acc)
        $fingerprint = $request->input('device_fingerprint');
        if ($fingerprint && User::where('device_fingerprint', $fingerprint)
            ->where('last_login_at', '>', now()->subHours(2))
            ->where('last_login_ip', '!=', $request->ip())
            ->exists()) {
            Log::alert('Suspicious login attempt', ['ip' => $request->ip(), 'fingerprint' => $fingerprint]);
            throw ValidationException::withMessages(['email' => 'Thiết bị không nhận diện được.']);
        }

        // 6. Validate form
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // 7. Xác định login bằng email hay telegram
        $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'telegram';
        $credentials = [$field => $request->email, 'password' => $request->password];

        // 8. Kiểm tra user + trạng thái
        $user = User::where($field, $request->email)->first();

        if (!$user || !$user->is_active) {
            RateLimiter::hit($ipKey);
            throw ValidationException::withMessages(['email' => 'Tài khoản không tồn tại hoặc chưa kích hoạt.']);
        }

        if ($user->is_banned || ($user->locked_until && $user->locked_until > now())) {
            throw ValidationException::withMessages(['email' => 'Tài khoản bị khóa tạm thời.']);
        }

        // 9. Đăng nhập
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'device_fingerprint' => $fingerprint ?? $user->device_fingerprint,
                'login_attempts' => 0,
                'locked_until' => null,
            ]);

            RateLimiter::clear($ipKey);
            RateLimiter::clear($globalKey);

            return redirect()->intended('home')
                ->with('success', 'Chào mừng trở lại, '.$user->name.'!');
        }

        // Sai mật khẩu
        $user->increment('login_attempts');
        if ($user->login_attempts >= 6) {
            $user->locked_until = now()->addMinutes(15);
            $user->save();
        }

        RateLimiter::hit($ipKey, 120);

        throw ValidationException::withMessages([
            'email' => 'Mật khẩu không đúng. Còn '.(6 - $user->login_attempts).' lần thử trước khi khóa.'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Đăng xuất thành công!');
    }
}