<!-- Sidebar Start -->
<aside class="left-sidebar with-vertical">
  <div>
    <!-- Start Vertical Layout Sidebar -->
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="/" class="text-nowrap logo-img">
        <img src="{{ asset('assets/images/logos/dark-logo.svg') }}" class="dark-logo" alt="Logo-Dark" />
        <img src="{{ asset('assets/images/logos/light-logo.svg') }}" class="light-logo" alt="Logo-light" />
      </a>
      <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
        <i class="ti ti-x"></i>
      </a>
    </div>

    <nav class="sidebar-nav scroll-sidebar" data-simplebar>
      <ul id="sidebarnav">
        
        <!-- Utama -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Utama</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/" aria-expanded="false">
            <span>
              <i class="ti ti-layout-dashboard"></i>
            </span>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <!-- Manajemen Pengajuan -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Manajemen Pengajuan</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-file-description"></i>
            </span>
            <span class="hide-menu">Data Pengajuan</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="#" aria-expanded="false">
            <span>
              <i class="ti ti-archive"></i>
            </span>
            <span class="hide-menu">Arsip Pengajuan</span>
          </a>
        </li>
        
      </ul>
    </nav>

    <!-- User Profile Card -->
    <div class="fixed-profile p-3 mx-4 mb-2 bg-secondary-subtle rounded mt-3">
      <div class="hstack gap-3">
        <div class="john-img">
          <img src="{{ asset('assets/images/profile/user-1.jpg') }}" class="rounded-circle" width="40" height="40" alt="profile" />
        </div>
        <div class="john-title">
          <h6 class="mb-0 fs-4 fw-semibold">{{ Auth::user()->username }}</h6>
          <span class="fs-2">Level {{ Auth::user()->level }}</span>
        </div>
        
        <!-- Logout Form -->
        <form method="POST" action="{{ route('logout') }}" class="ms-auto">
          @csrf
          <button type="submit" class="border-0 bg-transparent text-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Logout">
            <i class="ti ti-power fs-6"></i>
          </button>
        </form>

      </div>
    </div>
    <!-- End Vertical Layout Sidebar -->
  </div>
</aside>
<!--  Sidebar End -->