<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <img src="{{ asset($company->company_logo) }}" alt="Logo" width="200px">
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>
        @if(Auth::user()->server_manager === 1 || Auth::user()->role === 'super-admin' || Auth::user()->role === 'admin')
            <li class="menu-item {{ request()->routeIs('admin.server.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-server"></i>
                    <div data-i18n="Admin">Server Manager</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="" class="menu-link">
                            <div data-i18n="Company Settings">Servers</div>
                        </a>
                    </li>
                    @if(Auth::user()->role === 'super-admin')
                        <li class="menu-item {{ request()->routeIs('admin.server.commands*') ? 'active' : '' }}">
                            <a href="{{ route('admin.server.commands') }}" class="menu-link">
                                <div data-i18n="Company Settings">Command List</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif
        @if (Auth::user()->role === 'admin' || Auth::user()->role === 'super-admin')
            <li class="menu-item {{ request()->routeIs('admin.payroll.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div data-i18n="Admin">Payroll</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payroll.index')}}" class="menu-link">
                            <div data-i18n="Company Settings">Payroll</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('admin.payroll.employee.setup*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payroll.employee.setup')}}" class="menu-link">
                            <div data-i18n="Company Settings">Employee Setup</div>
                        </a>
                    </li>

                    <li class="menu-item {{ request()->routeIs('admin.payroll.monthly-expenses*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payroll.monthly-expenses')}}" class="menu-link">
                            <div data-i18n="Company Settings">Monthly Expenses</div>
                        </a>
                    </li>

                    <li class="menu-item {{ request()->routeIs('admin.payroll.onetime-expenses*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payroll.onetime-expenses')}}" class="menu-link">
                            <div>Onetime Expenses</div>
                        </a>
                    </li>

                </ul>
            </li>

            <li class="menu-item {{ request()->routeIs('admin.company.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-check-shield"></i>
                    <div data-i18n="Admin">Admin Panel</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item {{ request()->routeIs('admin.company.branding') ? 'active' : '' }}">
                        <a href="{{ route('admin.company.branding', ['tab' => 'company-image']) }}" class="menu-link">
                            <div>Update Branding</div>
                        </a>
                    </li>

                    <li class="menu-item {{ request()->routeIs('admin.company.company-info') ? 'active' : '' }}">
                        <a href="{{ route('admin.company.company-info') }}" class="menu-link">
                            <div>Update Company Info</div>
                        </a>
                    </li>

                    <li
                        class="menu-item {{ request()->routeIs('admin.company.users') || request()->routeIs('admin.company.user.edit') ? 'active' : '' }}">
                        <a href="{{ route('admin.company.users') }}" class="menu-link">
                            <div>Users</div>
                        </a>
                    </li>
                    <!-- Add more admin menu items here -->
                </ul>
            </li>

        @endif
        <li class="menu-item {{ request()->routeIs('profile.settings') ? 'active' : '' }}">
            <a href="{{ route('profile.settings') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Analytics">Settings</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <a href="{{ route('profile.edit') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Analytics">Profile</div>
            </a>
        </li>
    </ul>
</aside>
<!-- / Menu -->

<!-- Layout container -->
<div class="layout-page">
    <!-- Navbar -->

    <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
        id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
                {{-- <div class="nav-item d-flex align-items-center">
                    <i class="bx bx-search fs-4 lh-0"></i>
                    <input type="text" class="form-control border-0 shadow-none" placeholder="Search..."
                        aria-label="Search..." />
                </div> --}}
                <div class="nav-item d-flex align-items-center">
                    Planning in progress ....
                </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Place this tag where you want the button to render. -->
                {{-- <li class="nav-item lh-1 me-3">
            <a
              class="github-button"
              href="https://github.com/themeselection/sneat-html-admin-template-free"
              data-icon="octicon-star"
              data-size="large"
              data-show-count="true"
              aria-label="Star themeselection/sneat-html-admin-template-free on GitHub"
              >Star</a
            >
          </li> --}}

                <!-- User -->
                @if(Auth::user()->role === 'super-admin')
                    <span class="badge bg-danger">Super Admin Mode</span>
                @endif
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ asset($user->profile_photo) }}" alt class="w-px-40 h-auto rounded-circle" />
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="{{ asset($user->profile_photo) }}" alt
                                                class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block">{{ $user->first_name }}</span>
                                        <small class="text-muted">{{ $user->role }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bx bx-user me-2"></i>
                                <span class="align-middle">My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.settings') }}">
                                <i class="bx bx-cog me-2"></i>
                                <span class="align-middle">Settings</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="post">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bx bx-power-off me-2"></i>
                                    <span class="align-middle">Log Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
            </ul>
        </div>
    </nav>
