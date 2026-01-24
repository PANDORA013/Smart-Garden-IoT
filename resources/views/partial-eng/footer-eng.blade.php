<footer class="footer">
    <div class="container footer-font">
      <div class="row">
        <div class="col-md-6">
          <div class="contact-info">
            <i class="bi bi-geo-alt-fill"></i>
            <div class="footer-font">
              <p class="title">Head Office & AMDK Factory:</p>
              <p class="deskripsi">Jl. R.A. Kartini No.21 A Gresik, Jawa Timur 61122</p>
              <p class="title">Represntative Office:</p>
              <p class="deskripsi">Desa Sumberarum, Kecamatan Kerek</p>
              <p class="deskripsi">Tuban, 62356 Jawa Timur</p>
            </div>
          </div>
          <div class="contact-info">
            <i class="bi bi-envelope-fill"></i>
            <div class="footer-font">
              <p class="title">Email:</p>
              <p class="deskripsi">marketing@swabina.id</p>
            </div>
          </div>
          <div class="contact-info">
            <i class="bi bi-telephone-fill"></i>
            <div class="footer-font">
              <p class="title">Phone:</p>
              <p class="deskripsi">+62 31 3984719</p>
              <p class="deskripsi">+62 31 3985794</p>
              <p class="deskripsi">+62 356 711992</p>
              <p class="deskripsi">+62 356 711966</p>
            </div>
          </div>
        </div>
        <div class="col-md-6 text-md-end">
          <div class="social-icons">
            @if($social->facebook)
              <a class="social-icons" href="{{ $social->facebook }}" target="_blank">
                <i class="bi bi-facebook"></i>
              </a>
            @endif
            
            @if($social->youtube)
              <a href="{{ $social->youtube }}" target="_blank">
                <i class="bi bi-youtube"></i>
              </a>
            @endif
            
            @if($social->instagram)
              <a href="{{ $social->instagram }}" target="_blank">
                <i class="bi bi-instagram"></i>
              </a>
            @endif
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12 text-center mt-3 "  >
          <p id="copyright">&copy; 2024 Politeknik Negeri Jember. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>
