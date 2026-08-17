<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets') }}/images/logos/favicon.png" />

  <!-- Core Css -->
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}" />

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

  <title>SIPASTI Dikdasmen</title>

  <!-- Prevent FOUC for Dark Mode -->
  <script>
    const savedTheme = localStorage.getItem('sipasti_theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    }
  </script>
  <style>
    /* Fix DataTables length select dropdown overlapping text */
    .dataTables_length select.form-select {
      min-width: 65px !important;
      padding-right: 1.5rem !important;
      background-position: right 0.4rem center !important;
    }
  </style>
</head>

<body class="link-sidebar">
  <!-- Preloader -->
  <div class="preloader">
    <img src="{{ asset('assets') }}/images/logos/favicon.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    
    @include('layouts.partials.sidebar')
    
    <div class="page-wrapper">
      @include('layouts.partials.header')
      
      <div class="body-wrapper">
        <div class="container-fluid">
          @yield('content')
        </div>
      </div>
      
    </div>
  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <!-- Import Js Files -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/js/theme/app.init.js') }}"></script>
  <script src="{{ asset('assets/js/theme/theme.js') }}"></script>
  <script src="{{ asset('assets/js/theme/app.min.js') }}"></script>
  <script src="{{ asset('assets/js/theme/sidebarmenu.js') }}"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

  <!-- Global SweetAlert Logic -->
  <script>
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        timer: 3000,
        showConfirmButton: false
      });
    @endif
  </script>

  @stack('scripts')

  <!-- Save Theme to localStorage on Click and Force Display -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Force the correct display on load
      const savedTheme = localStorage.getItem('sipasti_theme') || 'light';
      if (savedTheme === 'dark') {
        document.querySelectorAll('.dark-logo').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.light-logo').forEach(el => el.style.display = 'flex');
        document.querySelectorAll('.moon').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.sun').forEach(el => el.style.display = 'flex');
      } else {
        document.querySelectorAll('.light-logo').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.dark-logo').forEach(el => el.style.display = 'flex');
        document.querySelectorAll('.sun').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.moon').forEach(el => el.style.display = 'flex');
      }

      // 2. Save on click and apply instantly
      document.querySelectorAll('.dark-layout').forEach(el => {
        el.addEventListener('click', () => {
          localStorage.setItem('sipasti_theme', 'dark');
          document.documentElement.setAttribute('data-bs-theme', 'dark');
          document.querySelectorAll('.dark-logo').forEach(e => e.style.display = 'none');
          document.querySelectorAll('.light-logo').forEach(e => e.style.display = 'flex');
          document.querySelectorAll('.moon').forEach(e => e.style.display = 'none');
          document.querySelectorAll('.sun').forEach(e => e.style.display = 'flex');
        });
      });
      document.querySelectorAll('.light-layout').forEach(el => {
        el.addEventListener('click', () => {
          localStorage.setItem('sipasti_theme', 'light');
          document.documentElement.setAttribute('data-bs-theme', 'light');
          document.querySelectorAll('.light-logo').forEach(e => e.style.display = 'none');
          document.querySelectorAll('.dark-logo').forEach(e => e.style.display = 'flex');
          document.querySelectorAll('.sun').forEach(e => e.style.display = 'none');
          document.querySelectorAll('.moon').forEach(e => e.style.display = 'flex');
        });
      });
    });
  </script>
</body>
</html>