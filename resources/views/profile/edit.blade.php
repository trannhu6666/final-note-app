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
                    <form>
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-4 shadow-sm"
                                style="width: 80px; height: 80px; font-size: 2.5rem;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label fw-semibold">Change Avatar</label>
                                <input type="file" class="form-control">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Display Name</label>
                            <input type="text" class="form-control form-control-lg" value="Student Name">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm">
                            <i class="bi bi-floppy me-2"></i> Save Profile
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h5 class="card-title border-bottom pb-2 mb-4 text-primary fw-bold">Change Password</h5>
                    <form>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-lg px-4 fw-bold">
                            <i class="bi bi-shield-lock me-2"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection