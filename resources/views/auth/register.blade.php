<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: #0d0d12;
        font-family: "Inter", sans-serif;
    }

    .ai-card {
        background: #111118;
        border: 1px solid #2a2a3d;
        border-radius: 16px;
        box-shadow: 0 0 25px rgba(100, 50, 255, 0.2);
    }

    .ai-title {
        background: linear-gradient(90deg, #a855f7, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .ai-input {
        background: #1a1a24;
        border: 1px solid #33334a;
        color: white;
    }

    .ai-input:focus {
        background: #1a1a24;
        border-color: #6d28d9;
        color: white;
        box-shadow: 0 0 0 0.25rem rgba(109, 40, 217, 0.3);
    }

    .ai-btn {
        background: linear-gradient(90deg, #7c3aed, #2563eb);
        border: none;
        color: white;
        font-weight: bold;
    }

    .ai-btn:hover {
        opacity: 0.85;
    }
</style>

<div class="container py-5">
    <div class="col-md-6 mx-auto ai-card p-4 p-md-5">

        <h2 class="text-center mb-2 ai-title fw-bold">Đăng Ký Tài Khoản AI</h2>
        <p class="text-center text-secondary mb-4">Truy cập hệ thống AI premium nhanh chóng</p>

        <form method="POST" action="{{ route('register.store') }}" id="registerForm">
            @csrf

            <!-- Honeypot -->
            <div class="d-none">
                <input type="text" name="website_url">
                <input type="hidden" name="form_submitted_at" value="{{ now()->timestamp }}">
            </div>

            <input type="hidden" name="h-captcha-response">

            <!-- Họ tên -->
            <div class="mb-3">
                <label class="form-label text-light">Họ tên</label>
                <input type="text" class="form-control ai-input" name="name" value="{{ old('name') }}">
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label text-light">Email</label>
                <input type="email" class="form-control ai-input" name="email" value="{{ old('email') }}">
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Mật khẩu -->
            <div class="mb-3">
                <label class="form-label text-light">Mật khẩu</label>
                <input type="password" class="form-control ai-input" name="password">
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Xác nhận mật khẩu -->
            <div class="mb-3">
                <label class="form-label text-light">Xác nhận mật khẩu</label>
                <input type="password" class="form-control ai-input" name="password_confirmation">
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- hCaptcha -->
            <div class="text-center mb-3">
                <div class="h-captcha" data-sitekey="{{ config('services.hcaptcha.sitekey') }}"></div>
            </div>
            @error('captcha')
                <small class="text-danger d-block text-center mb-2">{{ $message }}</small>
            @enderror

            <button class="btn w-100 ai-btn py-2 mt-2">
                Đăng Ký Ngay – Nhận 10.000đ
            </button>

            <p class="text-center text-secondary mt-3 mb-0" style="font-size: 12px;">
                Bằng cách đăng ký, bạn đồng ý với Điều khoản & Chính sách bảo mật.
            </p>
            <p class="text-center text-secondary mt-4 mb-0">
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="text-info fw-bold">Đăng nhập ngay →</a>
            </p>
        </form>
    </div>
</div>

<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/fingerprintjs2@2.1.0/fingerprint2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Fingerprint2().get(function(result) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'device_fingerprint';
            input.value = result;
            document.getElementById('registerForm').appendChild(input);
        });
    });
</script>
