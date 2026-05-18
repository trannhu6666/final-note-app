@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h3 class="fw-bold text-center mb-4 text-primary">Reset Password</h3>

                <form id="resetPasswordForm">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold uppercase">OTP Code</label>
                        <input type="text" id="otpInput" class="form-control form-control-lg text-center letter-spacing-3"
                            placeholder="• • • • • •" required maxlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" id="newPasswordInput" class="form-control"
                            placeholder="Enter your new password" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" id="confirmPasswordInput" class="form-control"
                            placeholder="Confirm your new password" required>
                    </div>

                    <button type="submit" id="btnResetPass" class="btn btn-primary btn-lg w-100 fw-bold">Save New
                        Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('resetPasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const urlParams = new URLSearchParams(window.location.search);
            const email = urlParams.get('email');

            if (!email) {
                alert('Không tìm thấy Email! Vui lòng làm lại từ bước Quên mật khẩu.');
                window.location.href = '/password/forgot';
                return;
            }

            const otp = document.getElementById('otpInput').value;
            const newPassword = document.getElementById('newPasswordInput').value;
            const confirmPassword = document.getElementById('confirmPasswordInput').value;

            if (newPassword !== confirmPassword) {
                return alert('Mật khẩu mới không khớp!');
            }

            const btn = document.getElementById('btnResetPass');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';
            btn.disabled = true;

            fetch('/api/auth/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    otp: otp,
                    password: newPassword,
                    password_confirmation: confirmPassword
                })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        alert('Đổi mật khẩu thành công! Hãy đăng nhập lại bằng mật khẩu mới.');
                        window.location.href = '/login';
                    } else {
                        alert(response.message || 'Mã OTP không hợp lệ hoặc đã hết hạn!');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Lỗi kết nối máy chủ!');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    </script>
@endsection