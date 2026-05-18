@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h3 class="fw-bold text-center mb-3 text-primary">Forgot Password?</h3>
                <p class="text-center text-muted mb-4">Enter your registered email address and we'll send you an OTP/Link to
                    reset your password.</p>

                <form id="forgotPasswordForm">
                    <div class="mb-4">
                        <label class="form-label">Email address</label>
                        <input type="email" id="emailInput" class="form-control form-control-lg"
                            placeholder="name@example.com" required>
                    </div>

                    <button type="submit" id="btnSendOTP" class="btn btn-primary btn-lg w-100 fw-bold">Send Reset
                        Link</button>
                </form>

                <div class="text-center mt-4">
                    <a href="/login" class="text-decoration-none text-primary"><i class="bi bi-arrow-left"></i> Back to
                        Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const email = document.getElementById('emailInput').value;
            const btn = document.getElementById('btnSendOTP');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
            btn.disabled = true;

            fetch('/api/auth/forgot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        alert('The OTP code has been sent! Please check your email.');
                        window.location.href = `/password/update?email=${encodeURIComponent(email)}`;
                    } else {
                        alert(response.message || 'No account found with this email address!');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Server connection error!');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    </script>
@endsection