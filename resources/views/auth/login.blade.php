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
                            <a href="/password/forgot" class="small text-decoration-none">Forgot password?</a>
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
                    e.preventDefault();

                    const emailValue = document.getElementById('loginEmail').value;
                    const passwordValue = document.getElementById('loginPassword').value;

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
                                localStorage.setItem('user_token', response.data.token);
                                localStorage.setItem('user_name', response.data.user.display_name);

                                window.location.href = '/notes';
                            } else {
                                if (response.message && response.message.includes('unverified')) {
                                    unverifiedAlert.classList.remove('d-none');
                                } else {
                                    alert(response.message || 'Login failed. Please check your credentials!');
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Login API error:', err);
                            alert('Cannot connect to the API server. Please make sure the Docker system is running!');
                        });
                });
            }
        });
    </script>
@endsection