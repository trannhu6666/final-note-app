@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h2 class="fw-bold text-center mb-4"><i class="bi bi-journal-text text-primary"></i> MyNotes</h2>

                <!-- Thông báo chưa kích hoạt -->
                <div class="alert alert-warning text-center d-none" role="alert">
                    Your account is unverified. Please check your email.
                </div>

                <form>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label">Password</label>
                            <a href="/password/reset" class="small text-decoration-none">Forgot password?</a>
                        </div>
                        <input type="password" class="form-control form-control-lg" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Login</button>
                </form>
                <div class="text-center mt-4">
                    Don't have an account? <a href="/register" class="fw-bold text-decoration-none">Register here</a>
                </div>
            </div>
        </div>
    </div>
@endsection