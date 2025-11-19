<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #0d0d12; font-family: "Inter", sans-serif; }
    .ai-card { background: #111118; border: 1px solid #2a2a3d; border-radius: 16px; box-shadow: 0 0 25px rgba(100,50,255,0.2); }
    .ai-title { background: linear-gradient(90deg, #a855f7, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .ai-input { background: #1a1a24; border: 1px solid #33334a; color: white; }
    .ai-input:focus { background: #1a1a24; border-color: #6d28d9; box-shadow: 0 0 0 0.25rem rgba(109,40,217,0.3); color: white; }
    .ai-btn { background: linear-gradient(90deg, #7c3aed, #2563eb); border: none; color: white; font-weight: bold; }
    .ai-btn:hover { opacity: 0.85; }
</style>

<div class="container py-5">
    <div class="col-md-6 mx-auto ai-card p-5">
        <h2 class="text-center mb-2 ai-title fw-bold">Đăng Nhập Tài Khoản</h2>
        <p class="text-center text-secondary mb-4">Quản lý tài khoản AI premium của bạn</p>

        <form method="POST" action="{{ route('login.store') }}" id="loginForm">
            @csrf

            {{-- <!-- HONEYPOT – CHỈ ĐƯỢC CÓ 1 LẦN -->
            <div class="d-none">
                <input type="text" name="my_website" tabindex="-1" autocomplete="off">
                <input type="hidden" name="form_submitted_at" value="{{ now()->timestamp }}">
            </div> --}}

            <!-- hCaptcha token -->
            <input type="hidden" name="h-captcha-response">

            <!-- Email hoặc Telegram -->
            <div class="mb-3">
                <label class="form-label text-light">Email</label>
                <input type="text" class="form-control ai-input" name="email" value="{{ old('email') }}" required autofocus>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- Mật khẩu -->
            <div class="mb-3">
                <label class="form-label text-light">Mật khẩu</label>
                <input type="password" class="form-control ai-input" name="password" required>
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- hCaptcha -->
            <div class="text-center mb-4">
                <div class="h-captcha" data-sitekey="{{ config('services.hcaptcha.sitekey') }}"></div>
                @error('captcha') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn w-100 ai-btn py-3">Đăng Nhập Ngay</button>
        </form>

        <p class="text-center text-secondary mt-4 mb-0">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="text-info fw-bold">Đăng ký miễn phí →</a>
        </p>
    </div>
</div>

<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/fingerprintjs2@2.1.0/fingerprint2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fingerprint
        new Fingerprint2().get(function(result) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'device_fingerprint';
            input.value = result;
            document.getElementById('loginForm').appendChild(input);
        });

        // Auto submit hCaptcha khi load xong (tăng UX)
        hcaptcha.render(document.querySelector('.h-captcha'), {
            sitekey: '{{ config('services.hcaptcha.sitekey') }}',
            callback: function(token) {
                document.querySelector('[name="h-captcha-response"]').value = token;
            }
        });
    });
</script>