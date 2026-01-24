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
            <a class="nav-link me-5"  id="nav-hamburger" href="{{ route('landingpageEng') }}" style="">Home</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item dropdown position-relative">
            <a id="nav-hamburger" class="nav-link dropdown-toggle me-5" href="#" id="navbarDropdown1" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="">
              Products and Services
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown1" style="">
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('facility-managementEng') }}" style="">SWA Facility Management</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('swasegarEng') }}" style="">SWA Segar</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('swatourEng') }}" style="">SWA Tour & Event Organizer</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('digitalsolutionEng') }}" style="">SWA Tech</a></li>
              <li><a class="dropdown-item" id="nav-hamburger"  href="{{ route('swaacademyEng') }}" style="">SWA Academy</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown position-relative">
            <a id="nav-hamburger"  class="nav-link dropdown-toggle me-5" href="#" id="navbarDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="">
              About Us
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown2" style="">
              <li><a class="dropdown-item" href="{{ route('tentangkamiEng') }}" style="">Company Overview</a></li>
              <li><a class="dropdown-item" href="{{ route('visimisiEng') }}" style="">Vision Mission & Culture</a></li>
              <li><a class="dropdown-item" href="{{ route('sertifEng') }}" style="">Certificates & Awards</a></li>
              <li><a class="dropdown-item" href="{{ route('kebijakandanpedomanEng') }}"  style="">Policy & Guideline</a></li>
            </ul>
          </li>

          <li class="nav-item">
            <a class="nav-link me-5"  id="nav-hamburger" href="{{ route('memilihkamiEng') }}" style="">Why Choose Us</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger"  href="{{ route('berita1212Eng') }}" style="">News</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger" href="{{ route('KarirEng') }}" style="">Career</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger"  href="{{ route('kontakkamiEng') }}" style="">Contact Us</a> <!-- Add me-5 for margin -->
          </li>

          <li class="nav-item">
            <a class="nav-link me-5" id="nav-hamburger" href="{{ route('landingpage') }}" >Id</a> <!-- Add me-5 for margin -->
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
