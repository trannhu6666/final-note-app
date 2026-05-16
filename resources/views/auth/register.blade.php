@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h2 class="fw-bold text-center mb-4"><i class="bi bi-person-plus text-primary"></i> Create Account</h2>

                <form>
                    <!-- Chỉ được phép chứa 4 trường này (Tiêu chí 1) -->
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Name</label>
                        <input type="text" class="form-control" placeholder="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Register</button>
                </form>

                <div class="text-center mt-4">
                    Already have an account? <a href="/login" class="fw-bold text-decoration-none">Login here</a>
                </div>
            </div>
        </div>
    </div>
@endsection