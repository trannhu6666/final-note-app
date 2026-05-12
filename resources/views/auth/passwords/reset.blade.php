@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h3 class="fw-bold text-center mb-4">Reset Password</h3>

                <form>
                    <!-- Có thể là input ẩn nếu dùng Link, hoặc input text nếu dùng OTP -->
                    <div class="mb-3">
                        <label class="form-label">OTP Code</label>
                        <input type="text" class="form-control" placeholder="Enter 6-digit OTP" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection