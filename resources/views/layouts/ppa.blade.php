<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin SWABINA GATRA</title>
<link rel="shortcut icon" type="image/png" href="https://th.bing.com/th/id/OIP.kAUISDUCtKkJbri2eOKW6gAAAA?rs=1&pid=ImgDetMain" />
<link rel="stylesheet" href="{{ asset('admin/css/styles.min.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Dark mode styles */
    [data-bs-theme="dark"] {
        --bs-body-color: #dee2e6;
        --bs-body-bg: #212529;
    }

    [data-bs-theme="dark"] .page-wrapper {
        background: #1e1e2d;
    }

    [data-bs-theme="dark"] .navbar {
        background: #1e1e2d !important;
        border-color: #2d2d3f !important;
    }

    [data-bs-theme="dark"] .left-sidebar {
        background: #1e1e2d;
        border-right: 1px solid #2d2d3f;
    }

    [data-bs-theme="dark"] .sidebar-nav ul .sidebar-item .sidebar-link {
        color: #cdcdde !important;
    }

    [data-bs-theme="dark"] .sidebar-nav ul .nav-small-cap {
        color: #9899ac !important;
    }

    [data-bs-theme="dark"] .sidebar-nav ul .sidebar-item .sidebar-link:hover,
    [data-bs-theme="dark"] .sidebar-nav ul .sidebar-item .sidebar-link.active {
        background: #2d2d3f;
        color: #fff !important;
    }

    [data-bs-theme="dark"] .card {
        background: #1e1e2d;
        border-color: #2d2d3f;
    }

    [data-bs-theme="dark"] .table {
        --bs-table-bg: #1e1e2d;
        --bs-table-border-color: #2d2d3f;
        color: #cdcdde;
    }

    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
        background-color: #1e1e2d;
        border-color: #2d2d3f;
        color: #cdcdde;
    }

    [data-bs-theme="dark"] .form-control:focus,
    [data-bs-theme="dark"] .form-select:focus {
        background-color: #2d2d3f;
        border-color: #3699ff;
        color: #cdcdde;
    }

    [data-bs-theme="dark"] .dropdown-menu {
        background-color: #1e1e2d;
        border-color: #2d2d3f;
    }

    [data-bs-theme="dark"] .dropdown-item {
        color: #cdcdde;
    }

    [data-bs-theme="dark"] .dropdown-item:hover {
        background-color: #2d2d3f;
        color: #fff;
    }

    [data-bs-theme="dark"] .modal-content {
        background-color: #1e1e2d;
        border-color: #2d2d3f;
    }

    [data-bs-theme="dark"] .modal-header,
    [data-bs-theme="dark"] .modal-footer {
        border-color: #2d2d3f;
    }

    [data-bs-theme="dark"] .border-bottom,
    [data-bs-theme="dark"] .border-top,
    [data-bs-theme="dark"] .border-start,
    [data-bs-theme="dark"] .border-end {
        border-color: #2d2d3f !important;
    }

    /* Theme toggle button styles */
    .theme-toggle {
        background: transparent;
        border: none;
        padding: 0.5rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .theme-toggle i {
        font-size: 1.2rem;
        color: inherit;
    }

    [data-bs-theme="dark"] .theme-toggle i {
        color: #cdcdde;
    }

    /* Scrollbar styles for dark mode */
    [data-bs-theme="dark"] ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    [data-bs-theme="dark"] ::-webkit-scrollbar-track {
        background: #1e1e2d;
    }

    [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
        background: #2d2d3f;
        border-radius: 4px;
    }

    [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover {
        background: #3699ff;
    }

    /* Navbar specific dark mode styles */
    [data-bs-theme="dark"] .app-header {
        background: #1e1e2d !important;
        border-bottom: 1px solid #2d2d3f;
    }

    [data-bs-theme="dark"] .nav-link {
        color: #cdcdde !important;
    }

    [data-bs-theme="dark"] .nav-link:hover {
        color: #ffffff !important;
    }

    [data-bs-theme="dark"] .navbar {
        background: #1e1e2d !important;
    }

    [data-bs-theme="dark"] .navbar-collapse {
        background: #1e1e2d;
    }

    [data-bs-theme="dark"] .dropdown-menu {
        background: #1e1e2d;
        border-color: #2d2d3f;
    }

    [data-bs-theme="dark"] .dropdown-item {
        color: #cdcdde;
    }

    [data-bs-theme="dark"] .dropdown-item:hover {
        background: #2d2d3f;
        color: #ffffff;
    }

    [data-bs-theme="dark"] .message-body {
        background: #1e1e2d;
    }

    [data-bs-theme="dark"] .btn-outline-primary {
        color: #cdcdde;
        border-color: #2d2d3f;
    }

    [data-bs-theme="dark"] .btn-outline-primary:hover {
        background: #2d2d3f;
        color: #ffffff;
        border-color: #3699ff;
    }

    /* Additional styles for better contrast */
    [data-bs-theme="dark"] .nav-icon-hover:hover {
        background: #2d2d3f !important;
    }

    [data-bs-theme="dark"] .ti {
        color: #cdcdde;
    }

    /* Navbar right side styles */
    [data-bs-theme="dark"] .navbar-nav .nav-link {
        color: #cdcdde !important;
        transition: color 0.3s ease;
    }

    [data-bs-theme="dark"] .navbar-nav .nav-link:hover {
        color: #ffffff !important;
    }

    .navbar-nav .nav-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
    }

    .navbar-nav .nav-link i {
        font-size: 1.1rem;
    }

    /* Optional: Add hover effect */
    .navbar-nav .nav-link:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    [data-bs-theme="dark"] .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    /* Navbar icon styles */
    .navbar-nav .nav-link {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }

    .navbar-nav .nav-link i {
        font-size: 0.95rem;
    }

    .theme-toggle {
        padding: 0.4rem 0.8rem !important;
    }

    .theme-toggle i {
        font-size: 0.95rem;
    }

    /* Hover effects */
    .nav-icon-hover {
        transition: all 0.2s ease;
    }

    .nav-icon-hover:hover {
        transform: scale(1.1);
    }

    [data-bs-theme="dark"] .nav-icon-hover:hover {
        color: #ffffff !important;
    }
</style>
@stack('styles')
</head>

<body>
<!--  Body Wrapper -->
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-center">
            <a href="{{ route('landingpage')}}" class="text-nowrap logo-img">
            {{-- <a href="{{ route('landingpage') }}" class="text-nowrap logo-img"> --}}
            <img src="{{ asset('assets/logo-perusahaan/LogaSWA_Biru_2cm-removebg-preview.png') }}" alt="Logo Swabina" width="50" height="50" />
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
            </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">
            <li class="nav-small-cap">
                <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{route('admin.dashboard')}}" aria-expanded="false">
                {{-- <a class="sidebar-link" href="{{ route('admin.dashboard') }}" aria-expanded="false"> --}}
                <span>
                    <iconify-icon icon="solar:home-smile-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Dashboard</span>
                </a>
            </li>
            <li class="nav-small-cap">
                <i class="ti ti-dots nav-small-cap-icon fs-6"></i>
                <span class="hide-menu">UI COMPONENTS</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                <span>
                    <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
                </span>
                <span class="hide-menu">Landing Page</span>
                <span class="ms-auto">
                    <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon"></iconify-icon>
                </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                <li class="sidebar-item">
                    <a href="{{ route('admin.landingpage.carousel.index') }}" class="sidebar-link">
                        <span class="hide-menu">Carousel</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{route('admin.landingpage.fotoLayanan.index')}}" class="sidebar-link">
                        <span class="hide-menu">Foto dan Layanan</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{route('admin.landingpage.jejaklangkah.index')}}" class="sidebar-link">
                        <span class="hide-menu">Jejak Langkah</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{route('admin.landingpage.sekilas.index')}}" class="sidebar-link">
                        <span class="hide-menu">Sekilas Perusahaan</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{route('admin.landingpage.sertifikat-penghargaan.index')}}" class="sidebar-link">
                        <span class="hide-menu">Sertifikat & Penghargaan</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{route('admin.landingpage.visimisi.index')}}" class="sidebar-link">
                        <span class="hide-menu">Visi Misi & Budaya</span>
                    </a>
                </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow d-flex justify-content-between align-items-center" href="javascript:void(0)" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-6"></iconify-icon>
                        <span class="hide-menu">Facility Management</span>
                    </span>
                    <span>
                        <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon-right"></iconify-icon>
                        <iconify-icon icon="akar-icons:chevron-down" class="dropdown-icon-down d-none"></iconify-icon>
                    </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.facilitymanagement.carouselFM.index') }}" class="sidebar-link">
                            <span class="hide-menu">Carousel</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.facilitymanagement.gambarFM.index') }}" class="sidebar-link">
                            <span class="hide-menu">Gambar</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.textfm.index') }}" class="sidebar-link">
                            <span class="hide-menu">Text Area</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow d-flex justify-content-between align-items-center" href="javascript:void(0)" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:bookmark-square-minimalistic-bold-duotone" class="fs-6"></iconify-icon>
                        <span class="hide-menu">Swa Segar</span>
                    </span>
                    <span>
                        <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon-right"></iconify-icon>
                        <iconify-icon icon="akar-icons:chevron-down" class="dropdown-icon-down d-none"></iconify-icon>
                    </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swasegar.carousel.index') }}" class="sidebar-link">
                            <span class="hide-menu">Carousel</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swasegar.gambarSS.index') }}" class="sidebar-link">
                            <span class="hide-menu">Gambar</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swasegar.textss.index') }}" class="sidebar-link">
                            <span class="hide-menu">Text</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow d-flex justify-content-between align-items-center" href="javascript:void(0)" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:file-text-bold-duotone" class="fs-6"></iconify-icon>
                        <span class="hide-menu">Swa Tour & Organizer</span>
                    </span>
                    <span>
                        <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon-right"></iconify-icon>
                        <iconify-icon icon="akar-icons:chevron-down" class="dropdown-icon-down d-none"></iconify-icon>
                    </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swatour.carouselteo.index') }}" class="sidebar-link">
                            <span class="hide-menu">Carousel</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swatour.gambarteo.index') }}" class="sidebar-link">
                            <span class="hide-menu">Gambar</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swatour.textteo.index') }}" class="sidebar-link">
                            <span class="hide-menu">Text</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow d-flex justify-content-between align-items-center" href="javascript:void(0)" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:text-field-focus-bold-duotone" class="fs-6"></iconify-icon>
                        <span class="hide-menu">Digital Solution</span>
                    </span>
                    <span>
                        <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon-right"></iconify-icon>
                        <iconify-icon icon="akar-icons:chevron-down" class="dropdown-icon-down d-none"></iconify-icon>
                    </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.digitalsolution.carouselds.index') }}" class="sidebar-link">
                            <span class="hide-menu">Carousel</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.digitalsolution.gambards.index') }}" class="sidebar-link">
                            <span class="hide-menu">Gambar </span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.digitalsolution.textds.index') }}" class="sidebar-link">
                            <span class="hide-menu">Text</span>
                        </a>
                    </li>
                </ul>
            </li>
             <li class="sidebar-item">
                <a class="sidebar-link has-arrow d-flex justify-content-between align-items-center" href="javascript:void(0)" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:text-field-focus-bold-duotone" class="fs-6"></iconify-icon>
                        <span class="hide-menu">swa Academy</span>
                    </span>
                    <span>
                        <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon-right"></iconify-icon>
                        <iconify-icon icon="akar-icons:chevron-down" class="dropdown-icon-down d-none"></iconify-icon>
                    </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swaacademy.index') }}" class="sidebar-link">
                            <span class="hide-menu">Carousel</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swaacademy.gambarSA.index') }}" class="sidebar-link">
                            <span class="hide-menu">Gambar </span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.swaacademy.textSA.index') }}" class="sidebar-link">
                            <span class="hide-menu">Text</span>
                        </a>
                    </li>
                </ul>
            </li> 
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow d-flex justify-content-between align-items-center" href="javascript:void(0)" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:text-field-focus-bold-duotone" class="fs-6"></iconify-icon>
                        <span class="hide-menu">Kontak kami</span>
                    </span>
                    <span>
                        <iconify-icon icon="akar-icons:chevron-right" class="dropdown-icon-right"></iconify-icon>
                        <iconify-icon icon="akar-icons:chevron-down" class="dropdown-icon-down d-none"></iconify-icon>
                    </span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                    <li class="sidebar-item">
                        <a href="{{ route('admin.kontakkami.carouselkk.index') }}" class="sidebar-link">
                            <span class="hide-menu">Carousel</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.kontakkami.gambarkk.index') }}" class="sidebar-link">
                            <span class="hide-menu">Gambar </span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.kontakkami.textkk.index') }}" class="sidebar-link">
                            <span class="hide-menu">Text</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('admin.kontakkami.faq.index') }}" class="sidebar-link">
                            <span class="hide-menu">FAQ</span>
                        </a>
                    </li>
                </ul>
            </li> 
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.memilihkami.mk.index') }}" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:text-field-focus-bold-duotone" class="fs-6"></iconify-icon>
                    </span>
                    <span class="hide-menu">Mengapa Memilih <br> Kami</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('admin.berita.berita.index') }}" aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:text-field-focus-bold-duotone" class="fs-6"></iconify-icon>
                    </span>
                    <span class="hide-menu">Berita</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('logout') }}" 
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                aria-expanded="false">
                    <span>
                        <iconify-icon icon="solar:login-3-bold-duotone" class="fs-6"></iconify-icon>
                    </span>
                    <span class="hide-menu">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
        <!--  Header Start -->
    <header class="app-header">
        <nav class="navbar navbar-expand-lg">
            <ul class="navbar-nav">
                <li class="nav-item d-block d-xl-none">
                    <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <button class="btn btn-link nav-link px-3 theme-toggle">
                        <i class="fas fa-sun theme-icon-light"></i>
                        <i class="fas fa-moon theme-icon-dark d-none"></i>
                    </button>
                </li>
            </ul>
            <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link nav-icon-hover" href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!--  Header End -->
    <div class="container-fluid">
        @yield('content')
    </div>
    </div>
</div>
<script src="{{ asset('admin/libs/jquery/dist/jquery.min.js')}}"> </script>
<script src="{{ asset('admin/libs/bootstrap/dist/js/bootstrap.bundle.min.js')}}"> </script>
<script src="{{ asset('admin/libs/apexcharts/dist/apexcharts.min.js')}}"> </script>
<script src="{{ asset('admin/libs/simplebar/dist/simplebar.js')}}"> </script>
<script src="{{ asset('admin/js/sidebarmenu.js')}}"> </script>
<script src="{{ asset('admin/js/app.min.js')}}"> </script>
<script src="{{ asset('admin/js/dashboard.js')}}"> </script>
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
<script>$(document).ready(function() {$('.sidebar-link.has-arrow').on('click', function() {var $this = $(this);var $submenu = $this.next('.collapse');$submenu.slideToggle(300);$this.find('.dropdown-icon').toggleClass('rotate');});});</script>
<style>
    .dropdown-icon.rotate {
    transform: rotate(90deg);
    transition: transform 0.3s;
    }
</style>
<script>
    const getPreferredTheme = () => {
        const savedTheme = localStorage.getItem('theme')
        if (savedTheme) {
            return savedTheme
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-bs-theme', theme)
        localStorage.setItem('theme', theme)
        
        // Toggle icons
        const lightIcon = document.querySelector('.theme-icon-light')
        const darkIcon = document.querySelector('.theme-icon-dark')
        
        if (theme === 'dark') {
            lightIcon.classList.add('d-none')
            darkIcon.classList.remove('d-none')
            // Additional dark mode specific adjustments
            document.querySelectorAll('.text-dark').forEach(element => {
                element.classList.remove('text-dark');
                element.classList.add('text-light');
            });
        } else {
            lightIcon.classList.remove('d-none')
            darkIcon.classList.add('d-none')
            // Additional light mode specific adjustments
            document.querySelectorAll('.text-light').forEach(element => {
                element.classList.remove('text-light');
                element.classList.add('text-dark');
            });
        }
    }

    // Set theme on load
    document.addEventListener('DOMContentLoaded', () => {
        setTheme(getPreferredTheme())
    })

    // Add toggle button handler
    document.querySelector('.theme-toggle').addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme')
        setTheme(currentTheme === 'dark' ? 'light' : 'dark')
    })

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (!localStorage.getItem('theme')) {
            setTheme(e.matches ? 'dark' : 'light')
        }
    })
</script>
@yield('scripts')
@stack('scripts')
</body>

</html>