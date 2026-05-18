@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3 class="fw-bold mb-4 text-primary">
                <i class="bi bi-person-lines-fill me-2"></i>Account Settings
            </h3>

            <div class="card shadow border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title border-bottom pb-2 mb-4 text-primary fw-bold">Profile Information</h5>
                    <form id="profileForm">
                        <div class="d-flex align-items-center mb-4">
                            <div id="avatarContainer"
                                class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-4 shadow-sm overflow-hidden"
                                style="width: 80px; height: 80px; font-size: 2.5rem;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label fw-semibold">Change Avatar</label>
                                <input type="file" id="avatarInput" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Display Name</label>
                            <input type="text" id="displayNameInput" class="form-control form-control-lg"
                                placeholder="Student Name" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm" id="btnSaveProfile">
                            <i class="bi bi-floppy me-2"></i> Save Profile
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h5 class="card-title border-bottom pb-2 mb-4 text-primary fw-bold">Change Password</h5>
                    <form id="passwordForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" id="currentPassword" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" id="newPassword" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" id="confirmNewPassword" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-lg px-4 fw-bold" id="btnUpdatePassword">
                            <i class="bi bi-shield-lock me-2"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function getAuthHeaders(isFormData = false) {
                const token = localStorage.getItem('user_token');
                const headers = {};
                if (!isFormData) headers['Content-Type'] = 'application/json';
                headers['Accept'] = 'application/json';
                if (token) headers['Authorization'] = `Bearer ${token}`;
                return headers;
            }

            // 🌟 1. TỰ ĐỘNG FETCH DỮ LIỆU THẬT TỪ DATABASE KHI VỪA VÀO TRANG PROFILE
            function loadUserProfile() {
                fetch('/api/user/profile', { method: 'GET', headers: getAuthHeaders() })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success' && response.data) {
                            const user = response.data;
                            document.getElementById('displayNameInput').value = user.display_name || '';
                            localStorage.setItem('user_name', user.display_name || '');

                            if (user.avatar_url) {
                                document.getElementById('avatarContainer').innerHTML = `<img src="${user.avatar_url}" style="width:100%; height:100%; object-fit:cover;" onerror="this.outerHTML='<i class=\\'bi bi-person\\'></i>'">`;
                            }
                        }
                    }).catch(err => console.error("Lỗi load profile:", err));
            }
            loadUserProfile();

            // 2. GỌI API CẬP NHẬT PROFILE (Tên + Avatar)
            document.getElementById('profileForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('btnSaveProfile');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
                btn.disabled = true;

                const formData = new FormData();
                formData.append('display_name', document.getElementById('displayNameInput').value);

                const avatarFile = document.getElementById('avatarInput').files[0];
                if (avatarFile) formData.append('avatar', avatarFile);

                fetch('/api/user/profile', {
                    method: 'POST',
                    headers: getAuthHeaders(true),
                    body: formData
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            alert('Cập nhật thông tin thành công!');
                            loadUserProfile(); // Tải lại để cập nhật ảnh đại diện mới ngay lập tức
                            document.getElementById('avatarInput').value = ''; // Reset input file
                        } else {
                            alert(response.message || 'Lỗi cập nhật!');
                        }
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        btn.innerHTML = '<i class="bi bi-floppy me-2"></i> Save Profile';
                        btn.disabled = false;
                    });
            });

            // 3. GỌI API ĐỔI MẬT KHẨU
            document.getElementById('passwordForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const currentPass = document.getElementById('currentPassword').value;
                const newPass = document.getElementById('newPassword').value;
                const confirmPass = document.getElementById('confirmNewPassword').value;

                if (newPass !== confirmPass) {
                    return alert("Mật khẩu mới không khớp!");
                }

                const btn = document.getElementById('btnUpdatePassword');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
                btn.disabled = true;

                fetch('/api/user/password', {
                    method: 'PUT',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        current_password: currentPass,
                        new_password: newPass
                    })
                })
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            alert('Đổi mật khẩu thành công!');
                            document.getElementById('passwordForm').reset();
                        } else {
                            alert(response.message || 'Mật khẩu cũ không đúng hoặc có lỗi xảy ra.');
                        }
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        btn.innerHTML = '<i class="bi bi-shield-lock me-2"></i> Update Password';
                        btn.disabled = false;
                    });
            });
        });
    </script>
@endsection
