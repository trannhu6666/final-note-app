@extends('layouts.guest')

@section('content')
    <section class="hero-section">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <i class="bi bi-cloud-check text-primary" style="font-size: 5rem;"></i>
                    <h1 class="display-4 fw-bold text-dark mt-3 mb-4">Smart Note Management</h1>
                    <p class="lead text-secondary mb-5">
                        Store, search, and share your notes anytime, anywhere.
                        Featuring auto-save and offline capabilities to keep you productive!
                    </p>

                    <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                        <a href="/login" class="btn btn-primary btn-lg px-4 gap-3">
                            <i class="bi bi-box-arrow-in-right"></i> Login Now
                        </a>
                        <a href="/register" class="btn btn-outline-secondary btn-lg px-4">
                            <i class="bi bi-person-plus"></i> Create New Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection