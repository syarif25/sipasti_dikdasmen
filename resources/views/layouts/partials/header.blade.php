<!--  Header Start -->
      <header class="topbar">
        <div class="with-vertical">
          <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
              <li class="nav-item nav-icon-hover-bg rounded-circle ms-n2">
                <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                  <i class="ti ti-menu-2"></i>
                </a>
              </li>
            </ul>

            <div class="d-block d-lg-none py-4">
              <a href="/" class="text-nowrap logo-img">
                <img src="{{ asset('assets/images/logos/dark-logo.svg') }}" class="dark-logo" alt="Logo-Dark" />
                <img src="{{ asset('assets/images/logos/light-logo.svg') }}" class="light-logo" alt="Logo-light" />
              </a>
            </div>
            
            <a class="navbar-toggler nav-icon-hover-bg rounded-circle p-0 mx-0 border-0" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <i class="ti ti-dots fs-7"></i>
            </a>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between">
                <a href="javascript:void(0)" class="nav-link nav-icon-hover-bg rounded-circle mx-0 ms-n1 d-flex d-lg-none align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar" aria-controls="offcanvasWithBothOptions">
                  <i class="ti ti-align-justified fs-7"></i>
                </a>
                
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                  <!-- Tema Gelap/Terang -->
                  <li class="nav-item nav-icon-hover-bg rounded-circle">
                    <a class="nav-link moon dark-layout" href="javascript:void(0)">
                      <i class="ti ti-moon moon"></i>
                    </a>
                    <a class="nav-link sun light-layout" href="javascript:void(0)">
                      <i class="ti ti-sun sun"></i>
                    </a>
                  </li>
                  
                  <!-- Profile Dropdown -->
                  @php
                    $levelMap = [
                        1 => 'Sekolah/Madrasah',
                        2 => 'Staf Bidang',
                        3 => 'Kasubag',
                        4 => 'Kabag',
                        5 => 'KTU',
                        6 => 'Kabid',
                        7 => 'Administrator',
                        8 => 'Super Admin'
                    ];
                    $levelName = $levelMap[Auth::user()->level] ?? 'Unknown';
                  @endphp
                  <li class="nav-item">
                    <div class="nav-link pe-0 d-flex align-items-center">
                      <div class="user-profile-img d-flex align-items-center">
                        <i class="ti ti-user-circle text-primary fs-8 bg-primary-subtle rounded-circle p-1" style="line-height: 1;"></i>
                      </div>
                      <div class="ms-3 d-none d-sm-block text-start">
                        <h6 class="mb-0 fs-3 fw-semibold text-dark">{{ Auth::user()->username }}</h6>
                        <span class="fs-2 text-muted">{{ $levelName }}</span>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
        </div>
      </header>
<!--  Header End -->
