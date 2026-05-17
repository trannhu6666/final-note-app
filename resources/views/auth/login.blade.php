@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h2 class="fw-bold text-center mb-4"><i class="bi bi-journal-text text-primary"></i> MyNotes</h2>

                <div class="alert alert-warning text-center d-none" id="unverifiedAlert" role="alert">
                    Your account is unverified. Please check your email.
                </div>

                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" id="loginEmail" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label">Password</label>
                            <a href="/password/reset" class="small text-decoration-none">Forgot password?</a>
                        </div>
                        <input type="password" id="loginPassword" class="form-control form-control-lg" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Login</button>
                </form>
                <div class="text-center mt-4">
                    Don't have an account? <a href="/register" class="fw-bold text-decoration-none">Register here</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');
            const unverifiedAlert = document.getElementById('unverifiedAlert');

            if (loginForm) {
                loginForm.addEventListener('submit', function (e) {
                    e.preventDefault(); // Ngăn trình duyệt tự động load lại trang khi bấm nút

                    // Lấy giá trị chữ mà người dùng đã gõ vào 2 ô nhập liệu
                    const emailValue = document.getElementById('loginEmail').value;
                    const passwordValue = document.getElementById('loginPassword').value;

                    // Gọi API đăng nhập đến Backend của Dev B (Đúng theo API Document số 2)
                    fetch('/api/auth/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: emailValue,
                            password: passwordValue
                        })
                    })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                // Lưu Token xác thực do Backend cấp vào bộ nhớ máy để các trang sau dùng (API số 3, 4, 5...)
                                localStorage.setItem('user_token', response.data.token);
                                localStorage.setItem('user_name', response.data.user.display_name);

                                // Chuyển hướng người dùng sang giao diện danh sách ghi chú (Personalized Homepage)
                                window.location.href = '/notes';
                            } else {
                                // Xử lý thông báo nếu tài khoản chưa kích hoạt hoặc sai thông tin
                                if (response.message && response.message.includes('unverified')) {
                                    unverifiedAlert.classList.remove('d-none');
                                } else {
                                    alert(response.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại tài khoản!');
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Lỗi khi gọi API đăng nhập:', err);
                            alert('Không thể kết nối đến máy chủ API. Hãy chắc chắn rằng hệ thống Docker đang bật nhé!');
                        });
                });
            }
        });
    </script>
@endsection