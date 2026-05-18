@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h3 class="fw-bold text-center mb-3 text-primary">Reset Password</h3>
                <p class="text-center text-muted mb-4">Please enter the 6-digit OTP sent to your email and set your new
                    password.</p>

                <form id="resetPasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" id="emailInput" class="form-control form-control-lg bg-light" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">OTP Verification Code</label>
                        <input type="text" id="otpInput" class="form-control form-control-lg text-center fw-bold"
                            placeholder="******" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" id="passwordInput" class="form-control form-control-lg"
                            placeholder="At least 6 characters" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" id="passwordConfirmInput" class="form-control form-control-lg"
                            placeholder="Repeat your new password" required>
                    </div>

                    <button type="submit" id="btnResetPassword" class="btn btn-primary btn-lg w-100 fw-bold">Update
                        Password</button>
                </form>

                <div class="text-center mt-4">
                    <a href="/password/forgot" class="text-decoration-none text-primary"><i class="bi bi-arrow-left"></i>
                        Back to Request OTP</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. Tự động lấy Email từ URL (?email=name@example.com) và điền vào ô input
        const urlParams = new URLSearchParams(window.location.search);
        const emailParam = urlParams.get('email');
        if (emailParam) {
            document.getElementById('emailInput').value = emailParam;
        }

        // 2. Xử lý sự kiện Submit Form gửi dữ liệu lên API Reset mật khẩu
        document.getElementById('resetPasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('emailInput').value;
            const otp = document.getElementById('otpInput').value;
            const password = document.getElementById('passwordInput').value;
            const passwordConfirmation = document.getElementById('passwordConfirmInput').value;
            const btn = document.getElementById('btnResetPassword');
            const originalText = btn.innerHTML;

            // Kiểm tra khớp mật khẩu ở Frontend trước để đỡ mất thời gian gọi API
            if (password !== passwordConfirmation) {
                alert('Confirm password does not match!');
                return;
            }

            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            btn.disabled = true;

            // Gọi đúng endpoint định nghĩa trong api.php
            fetch('/api/auth/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    otp: otp,
                    password: password,
                    password_confirmation: passwordConfirmation // Phải có trường này để pass qua rule 'confirmed' của Laravel
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        alert('Password reset successful! You can now log in with your new password.');
                        window.location.href = '/login'; // Thành công thì đá về trang Login
                    } else {
                        alert(response.message || 'Invalid or expired OTP code!');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Server connection error or invalid request!');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    </script>
@endsection