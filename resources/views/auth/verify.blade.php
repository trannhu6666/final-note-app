@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5 text-center">
            <div class="card shadow border-0 p-5">
                <div id="verifyStatus">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h3 class="fw-bold text-primary">Verifying Account...</h3>
                    <p class="text-muted">Please wait while we activate your account.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Lấy email và token từ đường dẫn URL do email gửi tới
            const urlParams = new URLSearchParams(window.location.search);
            const email = urlParams.get('email');
            const token = urlParams.get('token');

            if (!email || !token) {
                document.getElementById('verifyStatus').innerHTML = `
                        <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                        <h3 class="fw-bold text-danger mt-3">Invalid Link</h3>
                        <p class="text-muted">This activation link is invalid or broken.</p>
                        <a href="/login" class="btn btn-primary mt-3">Go to Login</a>
                    `;
                return;
            }

            // GỌI API KÍCH HOẠT XUỐNG BACKEND
            fetch('/api/auth/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email, token: token })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        document.getElementById('verifyStatus').innerHTML = `
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                            <h3 class="fw-bold text-success mt-3">Account Activated!</h3>
                            <p class="text-muted">Your account is now fully verified.</p>
                            <a href="/" class="btn btn-success mt-3 px-4">Go to MyNotes</a>
                        `;
                    } else {
                        document.getElementById('verifyStatus').innerHTML = `
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            <h3 class="fw-bold text-warning mt-3">Activation Failed</h3>
                            <p class="text-muted">${response.message || 'The link may have expired.'}</p>
                            <a href="/login" class="btn btn-primary mt-3">Back to Login</a>
                        `;
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('verifyStatus').innerHTML = `
                        <h3 class="fw-bold text-danger">Server Error!</h3>
                    `;
                });
        });
    </script>
@endsection