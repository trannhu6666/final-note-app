@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h2 class="fw-bold text-center mb-4"><i class="bi bi-person-plus text-primary"></i> Create Account</h2>

                <form id="registerForm">
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" id="regEmail" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Name</label>
                        <input type="text" id="regName" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" id="regPassword" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" id="regConfirmPassword" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Register</button>
                </form>

                <div class="text-center mt-4">
                    Already have an account? <a href="/login" class="fw-bold text-decoration-none">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const registerForm = document.getElementById('registerForm');

            if (registerForm) {
                registerForm.addEventListener('submit', function (e) {
                    e.preventDefault(); // Chặn hành vi tự động tải lại trang của trình duyệt

                    // Lấy chính xác giá trị từ 4 ô nhập liệu
                    const emailValue = document.getElementById('regEmail').value;
                    const displayNameValue = document.getElementById('regName').value;
                    const passwordValue = document.getElementById('regPassword').value;
                    const confirmPasswordValue = document.getElementById('regConfirmPassword').value;

                    // Kiểm tra nhanh ở Frontend xem hai mật khẩu có khớp nhau không
                    if (passwordValue !== confirmPasswordValue) {
                        alert('Mật khẩu xác nhận không trùng khớp!');
                        return;
                    }

                    // Gọi API số 1: POST /api/auth/register
                    fetch('/api/auth/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: emailValue,
                            display_name: displayNameValue,
                            password: passwordValue,
                            password_confirmation: confirmPasswordValue
                        })
                    })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                alert(response.message || 'Đăng ký tài khoản thành công! Hệ thống đang tự động đăng nhập...');

                                return fetch('/api/auth/login', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ email: emailValue, password: passwordValue })
                                });
                            } else {
                                if (response.message && typeof response.message === 'object') {
                                    // Gom tất cả các thông báo lỗi (ví dụ lỗi password ngắn, lỗi trùng email...) lại thành chuỗi chữ
                                    let errorText = '';
                                    for (let key in response.message) {
                                        errorText += response.message[key].join('\n') + '\n';
                                    }
                                    alert(errorText); // Hiển thị: "The password field must be at least 6 characters."
                                } else {
                                    alert(response.message || 'Đăng ký thất bại. Vui lòng thử lại!');
                                }
                                throw new Error('Registration logic failed on backend');
                            }
                        })
                        .then(res => res ? res.json() : null)
                        .then(loginResponse => {
                            if (loginResponse && loginResponse.status === 'success') {
                                // Lưu Token và tên hiển thị vào máy giống màn hình Login
                                localStorage.setItem('user_token', loginResponse.data.token);
                                localStorage.setItem('user_name', loginResponse.data.user.display_name);

                                // Đưa thẳng user vào personalized homepage để xem note luôn
                                window.location.href = '/';
                            }
                        })
                        .catch(err => {
                            console.error('Lỗi trong quá trình đăng ký/tự động đăng nhập:', err);
                        });
                });
            }
        });
    </script>
@endsection