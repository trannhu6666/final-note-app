@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h3 class="fw-bold text-center mb-3">Forgot Password?</h3>
                <p class="text-center text-muted mb-4">Enter your registered email address and we'll send you an OTP/Link to
                    reset your password.</p>

                <form>
                    <div class="mb-4">
                        <label class="form-label">Email address</label>
                        <input type="email" class="form-control form-control-lg" required>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">Send Reset Link</button>
                </form>

                <div class="text-center mt-4">
                    <a href="/login" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to Login</a>
                </div>
            </div>
        </div>
    </div>
@endsection