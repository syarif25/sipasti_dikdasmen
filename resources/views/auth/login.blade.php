@extends('layouts.auth')

@section('content')
  <div id="main-wrapper" class="auth-customizer-none">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100">
      <div class="position-relative z-index-5">
        <div class="row">
          <div class="col-xl-7 col-xxl-8">
            <a href="/" class="text-nowrap logo-img d-block px-4 py-9 w-100">
              <img src="{{ asset('assets/images/logos/dark-logo.svg') }}" class="dark-logo" alt="Logo-Dark" />
              <img src="{{ asset('assets/images/logos/light-logo.svg') }}" class="light-logo" alt="Logo-light" />
            </a>
            <div class="d-none d-xl-flex align-items-center justify-content-center h-n80">
              <img src="{{ asset('assets/images/backgrounds/login-security.svg') }}" alt="modernize-img" class="img-fluid" width="500">
            </div>
          </div>
          <div class="col-xl-5 col-xxl-4">
            <div class="authentication-login min-vh-100 bg-body row justify-content-center align-items-center p-4">
              <div class="auth-max-width col-sm-8 col-md-6 col-xl-7 px-4">
                <h2 class="mb-1 fs-7 fw-bolder">Welcome to SIPASTI</h2>
                <p class="mb-7">Your Admin Dashboard</p>
                <form>
                  <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                  </div>
                  <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1">
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
                      <label class="form-check-label text-dark fs-3" for="flexCheckChecked">
                        Remember this Device
                      </label>
                    </div>
                    <a class="text-primary fw-medium fs-3" href="#">Forgot Password ?</a>
                  </div>
                  <a href="/" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Sign In</a>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
