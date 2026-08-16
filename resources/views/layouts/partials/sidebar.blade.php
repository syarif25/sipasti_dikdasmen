<!-- Sidebar Start -->
<aside class="left-sidebar with-vertical">
  <div>
    <!-- Start Vertical Layout Sidebar -->
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="/" class="text-nowrap logo-img text-decoration-none">
        <span class="fw-bolder fs-5 text-uppercase text-primary" style="letter-spacing: 1px;">Sistem Informasi</span>
      </a>
      <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
        <i class="ti ti-x"></i>
      </a>
    </div>

    <nav class="sidebar-nav scroll-sidebar" data-simplebar>
      <ul id="sidebarnav">
        
        <!-- ============================================== -->
        <!-- MENU GLOBAL (SEMUA LEVEL)                      -->
        <!-- ============================================== -->
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

        <li class="sidebar-item">
          <a class="sidebar-link" href="/pengajuan" aria-expanded="false">
            <span>
              <i class="ti ti-file-description"></i>
            </span>
            <span class="hide-menu">Pengajuan</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/arsip" aria-expanded="false">
            <span>
              <i class="ti ti-archive"></i>
            </span>
            <span class="hide-menu">Arsip</span>
          </a>
        </li>
        
        <!-- ============================================== -->
        <!-- GOD MODE (SUPER ADMIN)                         -->
        <!-- Asumsi Super Admin = Level 7                   -->
        <!-- ============================================== -->
        @if(Auth::check() && Auth::user()->level >= 7)
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">God Mode</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/pengguna" aria-expanded="false">
            <span>
              <i class="ti ti-users"></i>
            </span>
            <span class="hide-menu">Data Pengguna</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/manajemen-pengajuan" aria-expanded="false">
            <span>
              <i class="ti ti-files"></i>
            </span>
            <span class="hide-menu">Manajemen Pengajuan</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/manajemen-log" aria-expanded="false">
            <span>
              <i class="ti ti-history"></i>
            </span>
            <span class="hide-menu">Manajemen Log</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link has-arrow {{ request()->is('master-data*') ? 'active' : '' }}" href="#masterDataCollapse" data-bs-toggle="collapse" aria-expanded="{{ request()->is('master-data*') ? 'true' : 'false' }}">
            <span class="d-flex">
              <i class="ti ti-box-multiple"></i>
            </span>
            <span class="hide-menu">Master Data</span>
          </a>
          <ul id="masterDataCollapse" class="collapse first-level {{ request()->is('master-data*') ? 'show' : '' }}">
            <li class="sidebar-item">
              <a href="{{ route('master.lembaga.index') }}" class="sidebar-link {{ request()->routeIs('master.lembaga.*') ? 'active' : '' }}">
                <div class="round-16 d-flex align-items-center justify-content-center">
                  <i class="ti ti-circle"></i>
                </div>
                <span class="hide-menu">Data Lembaga</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('master.jabatan.index') }}" class="sidebar-link {{ request()->routeIs('master.jabatan.*') ? 'active' : '' }}">
                <div class="round-16 d-flex align-items-center justify-content-center">
                  <i class="ti ti-circle"></i>
                </div>
                <span class="hide-menu">Data Jabatan</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('master.jenis-surat.index') }}" class="sidebar-link {{ request()->routeIs('master.jenis-surat.*') ? 'active' : '' }}">
                <div class="round-16 d-flex align-items-center justify-content-center">
                  <i class="ti ti-circle"></i>
                </div>
                <span class="hide-menu">Jenis Surat</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('master.tahun-akademik.index') }}" class="sidebar-link {{ request()->routeIs('master.tahun-akademik.*') ? 'active' : '' }}">
                <div class="round-16 d-flex align-items-center justify-content-center">
                  <i class="ti ti-circle"></i>
                </div>
                <span class="hide-menu">Tahun Akademik</span>
              </a>
            </li>
          </ul>
        </li>
        @endif
        
        <!-- ============================================== -->
        <!-- AKUN                                           -->
        <!-- ============================================== -->
        <li class="nav-small-cap">
          <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
          <span class="hide-menu">Akun</span>
        </li>
        <li class="sidebar-item mb-5">
          <form method="POST" action="{{ route('logout') }}" id="logout-form" class="d-none">
             @csrf
          </form>
          <a class="sidebar-link text-danger" href="javascript:void(0)" onclick="document.getElementById('logout-form').submit();" aria-expanded="false">
            <span>
              <i class="ti ti-power"></i>
            </span>
            <span class="hide-menu">Log Out</span>
          </a>
        </li>
      </ul>
    </nav>

    <!-- End Vertical Layout Sidebar -->
    <!-- End Vertical Layout Sidebar -->
  </div>
</aside>
<!--  Sidebar End -->