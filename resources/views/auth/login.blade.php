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
                <h2 class="mb-1 fs-7 fw-bolder">Welcome to DIKDASMEN</h2>
                <p class="mb-7">Your Admin Dashboard</p>
                <form action="{{ route('login') }}" method="POST">
                  @csrf
                  <div class="mb-3">
                    <label for="login" class="form-label">Username / Email</label>
                    <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login') }}" required autofocus>
                    @error('login')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                      <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="ti ti-eye"></i>
                      </button>
                      @error('password')
                          <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                  <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" name="remember" id="remember">
                      <label class="form-check-label text-dark fs-3" for="remember">
                        Remember this Device
                      </label>
                    </div>
                    <a class="text-primary fw-medium fs-3" href="#">Forgot Password ?</a>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Sign In</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const togglePassword = document.querySelector('#togglePassword');
      const password = document.querySelector('#password');
      if (togglePassword && password) {
        togglePassword.addEventListener('click', function (e) {
          const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
          password.setAttribute('type', type);
          const icon = this.querySelector('i');
          if (type === 'password') {
            icon.classList.remove('ti-eye-off');
            icon.classList.add('ti-eye');
          } else {
            icon.classList.remove('ti-eye');
            icon.classList.add('ti-eye-off');
          }
        });
      }
    });
  </script>
@endsection
