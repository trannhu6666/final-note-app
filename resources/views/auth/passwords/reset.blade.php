@extends('layouts.guest')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-5">
            <div class="card shadow border-0 p-4">
                <h3 class="fw-bold text-center mb-4 text-primary">Reset Password</h3>

                <form>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold uppercase">OTP Code</label>
                        <input type="text" class="form-control form-control-lg text-center letter-spacing-3"
                            placeholder="• • • • • •" required maxlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" placeholder="Enter your new password" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" placeholder="Confirm your new password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Save New Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection