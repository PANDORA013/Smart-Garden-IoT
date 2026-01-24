<nav class="navbar navbar-expand-lg custom-navbar sticky-top" style="background-color: #0454a3; border-radius: 0px; width: 100%; padding:1px; margin:auto; box-shadow: 0px 15px 15px -5px rgba(0, 0, 0, 0.5)">
    <div class="container-fluid navigasi">
        <img id="logo-resp" src="/assets/gambar_landingpage/logo_swabina.png" alt="">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav" style="border-radius: 15px;">
        <button type="button" class="close-btn" aria-label="Close">
            &times; <!-- Ini adalah simbol X -->
        </button>
        <ul class="navbar-nav mx-auto">

          <li class="nav-item">
            <a class="nav-link me-5"  id="nav-hamburger" href="{{ route('landingpage') }}" style="">Beranda</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item dropdown position-relative">
            <a id="nav-hamburger" class="nav-link dropdown-toggle me-5" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="">
              Produk dan Layanan
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown1" style="">
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('facility-management') }}" style="">SWA Facility Management</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('swasegar') }}" style="">SWA Segar</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('swatour') }}" style="">SWA Tour & Event Organizer</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('digitalsolution') }}" style="">SWA Tech</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('swaacademy') }}" style="">SWA Academy</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown position-relative">
            <a id="nav-hamburger"  class="nav-link dropdown-toggle me-5" href="#" id="navbarDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="">
              Tentang Kami
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown2" style="">
              <li><a class="dropdown-item" href="{{ route('sekilas') }}" style="">Sekilas Perusahaan</a></li>
              <li><a class="dropdown-item" href="{{ route('visimisi') }}" style="">Visi & Misi Perusahaan</a></li>
              <li><a class="dropdown-item" href="{{ route('sertif') }}" style="">Sertifikat & Penghargaan</a></li>
              <li><a class="dropdown-item" href="{{ route('kebijakandanpedoman') }}" >Kebijakan & Pedoman</a></li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link me-5"  id="nav-hamburger" href="{{ route('memilihkami') }}" style="">Mengapa Memilih Kami</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger"  href="{{ route('berita1212') }}" style="">Berita</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger" href="{{ route('Karir') }}"style="">Karir</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger"  href="{{ route('kontakkami') }}" style="">Kontak Kami</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger" href="{{ route('landingpageEng') }}" >Eng</a> <!-- Add me-5 for margin -->
          </li>

          {{-- logo dan gambar --}}


        </ul>
        <ul class="horizontal-list">
            <li>
                <img id="img-footer-hamburger" src="/assets/gambar_landingpage/logo_iso1.png" alt="">
            </li>
            <li>
                <img id="img-footer-hamburger" src="/assets/gambar_landingpage/logo_iso2.png" alt="">
            </li>
            <li>
                <img id="img-footer-hamburger" src="/assets/gambar_landingpage/logo_iso3.png" alt="">
            </li>
            <li>
                <img id="img-footer-hamburger" src="/assets/gambar_landingpage/logo_smk3.png" alt="">
            </li>
        </ul>
                <img id="img-footer-hamburger2" src="/assets/gambar_landingpage/industri.png" alt="">
      </div>
    </div>
  </nav>
