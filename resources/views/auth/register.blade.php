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
                    e.preventDefault();

                    const emailValue = document.getElementById('regEmail').value;
                    const displayNameValue = document.getElementById('regName').value;
                    const passwordValue = document.getElementById('regPassword').value;
                    const confirmPasswordValue = document.getElementById('regConfirmPassword').value;

                    if (passwordValue !== confirmPasswordValue) {
                        alert('Passwords do not match!');
                        return;
                    }

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
                                alert(response.message || 'Registration successful! Automatically logging in...');

                                return fetch('/api/auth/login', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ email: emailValue, password: passwordValue })
                                });
                            } else {
                                if (response.message && typeof response.message === 'object') {
                                    let errorText = '';
                                    for (let key in response.message) {
                                        errorText += response.message[key].join('\n') + '\n';
                                    }
                                    alert(errorText);
                                } else {
                                    alert(response.message || 'Registration failed. Please try again!');
                                }
                                throw new Error('Registration logic failed on backend');
                            }
                        })
                        .then(res => res ? res.json() : null)
                        .then(loginResponse => {
                            if (loginResponse && loginResponse.status === 'success') {
                                localStorage.setItem('user_token', loginResponse.data.token);
                                localStorage.setItem('user_name', loginResponse.data.user.display_name);

                                window.location.href = '/';
                            }
                        })
                        .catch(err => {
                            console.error('Registration/Login error:', err);
                        });
                });
            }
        });
    </script>
@endsection