<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PT Swabina Gatra Official Website</title>
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/landingPage.css')}}">
</head>
<body>
 <!-- Header / Top Bar -->
 <div class="container-fluid bg-white py-2 topheader" style="padding:0; border-bottom: 25px solid white;">
  <div class="container d-flex justify-content-between align-items-center px-5">
      <img src="/assets/gambar_landingpage/logo_swabina.png" alt="Company Logo" class="img-fluid" style="width: 80px; height: 80px;">
      <div class="d-flex align-items-center">
          <div class="d-flex align-items-center me-5">
              <i class="bi bi-geo-alt-fill me-2" style="font-size: 50px; color: #0454a3;"></i>
              <div class="d-flex flex-column me-4 deskripsi-alamat" style="color: #0454a3;">
                  <span>Alamat</span>
                  <span>Kantor Pusat & Pabrik AMDK:</span>
                  <span>Jl. R.A. Kartini No.21 A Gresik 61122,</span>
                  <span>Jawa Timur</span>
              </div>
          </div>
          <img src="/assets/gambar_landingpage/logo_iso1.png" alt="Logo 1" class="img-fluid logoIso" >
          <img src="/assets/gambar_landingpage/logo_iso2.png" alt="Logo 2" class="img-fluid logoIso" >
          <img src="/assets/gambar_landingpage/logo_iso3.png" alt="Logo 3" class="img-fluid logoIso" >
          <img src="/assets/gambar_landingpage/logo_smk3.png" alt="Logo 4" class="img-fluid logoIso" >
      </div>
  </div>
</div>

    <!-- Navbar -->
   @include('partial.navigasi')

<!-- Carousel -->
<div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-pause="false" style="">
    <div class="carousel-inner">
        @foreach($carousels as $key => $carousel)
            <div class="carousel-item {{ $key == 0 ? 'active' : '' }} carousel-awal">
                <img src="{{ asset('storage/public/' . $carousel->image) }}" class="d-block w-100 carousel-image" alt="{{ $carousel->title }}">
            </div>
        @endforeach
    </div>

    <div class="carousel-controls" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 1000;">
        @foreach($carousels as $key => $carousel)
            <span class="dot" data-bs-target="#carouselExampleFade" data-bs-slide-to="{{ $key }}" {{ $key == 0 ? 'class="active"' : '' }} style="width: 12px; height: 12px; background-color: white; border-radius: 50%; display: inline-block; transition: background-color 0.3s ease;"></span>
        @endforeach
    </div>
</div>

  <!-- Produk dan Layanan -->
  <section id="produk-layanan">
    <!-- Judul Section -->
    <div class="text-center mb-4">
        <h1 class="text-white fw-bold judul-pl">Produk dan Layanan</h1>
    </div>

    <!-- Wrapper untuk card, tambahkan justify-content-center untuk memusatkan -->
    <div class="d-flex flex-wrap justify-content-center" style="gap: 1rem;">
      
        <div class="card p-3 bg-body-tertiary" style="">
          <img src="/assets/gambar_landingpage/kerja.png" alt="..." class="card-img-top mx-auto" style="">
          <div class="card-body text-center">
            <h4 class="card-text fw-bold" style="color:#0454a3;">SWA</h4>
            <h5 class="card-text fw-bold">Facility Management</h5>
          </div>
        </div>

        <div class="card p-3 bg-body-tertiary" style="">
          <img src="/assets/gambar_landingpage/air.png" alt="..." class="card-img-top mx-auto" style="">
          <div class="card-body text-center">
            <h4 class="card-text fw-bold" style="color:#0454a3;">SWA</h4>
            <h5 class="card-text fw-bold">Segar</h5>
          </div>
        </div>

        <div class="card p-3 bg-body-tertiary" style="">
          <img src="/assets/gambar_landingpage/tour.png" alt="..." class="card-img-top mx-auto" style="">
          <div class="card-body text-center">
            <h4 class="card-text fw-bold" style="color:#0454a3;">SWA</h4>
            <h5 class="card-text fw-bold">Tour & Event Organizer</h5>
          </div>
        </div>

        <div class="card p-3 bg-body-tertiary" style="">
          <img src="/assets/gambar_landingpage/academy.png" alt="..." class="card-img-top mx-auto" style="">
          <div class="card-body text-center">
            <h4 class="card-text fw-bold" style="color:#0454a3;">SWA</h4>
            <h5 class="card-text fw-bold">Academy</h5>
          </div>
        </div>

        <div class="card p-3 bg-body-tertiary" style="">
          <img src="/assets/gambar_landingpage/digital.png" alt="..." class="card-img-top mx-auto" style="">
          <div class="card-body text-center">
            <h4 class="card-text fw-bold" style="color:#0454a3;">SWA</h4>
            <h5 class="card-text fw-bold">Tech</h5>
          </div>
        </div>
    </div>
</section>


    <!-- Carousel responsive -->
    @include('beranda.partial-beranda.carousel-responsive')

<!--Tentang Kami-->
<section>
  <div class="container mt-1">
    <h1 id="tentang-kami" class="text-center">Tentang Kami</h1>
    <ul class="nav nav-pills justify-content-center mt-3" id="aboutTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active about-tab" id="overview-tab" data-bs-toggle="pill" href="#overview  " role="tab" aria-controls="overview" aria-selected="true" >Sekilas Perusahaan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link about-tab" id="milestones-tab" data-bs-toggle="pill" href="#milestones " role="tab" aria-controls="milestones" aria-selected="false" >Jejak Langkah</a>
        </li>
        <li class="nav-item">
            <a class="nav-link about-tab" id="vision-tab" data-bs-toggle="pill" href="#vision " role="tab" aria-controls="vision" aria-selected="false" >Visi Misi dan Budaya</a>
        </li>
        <li class="nav-item">
            <a class="nav-link about-tab" id="certificates-tab" data-bs-toggle="pill" href="#certificates " role="tab" aria-controls="certificates" aria-selected="false" >Sertifikat dan Penghargaan</a>
        </li>
    </ul>
  
    <!--Sekilas Perusahaan-->
    
    <div class="tab-content mt-4" id="aboutTabContent">
      <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
        @foreach($sekilas as $sekilasItem)
          <p class="font-tentang-perusahaan" style="text-align: {{ $sekilasItem->text_align }};">
              {!! nl2br(e($sekilasItem->maintext['id'])) !!}
          </p>
      @endforeach
      <a href="{{ route('sekilas') }}" id="btn-tentang" role="button" class="btn btn-primary btn-lg"
      onmouseover="this.style.backgroundColor='#0d6efd';" 
         onmouseout="this.style.backgroundColor='#0454a3';">
         Baca Selengkapnya
      </a>
  </div>

<!--Visi dan Misi-->
<div class="tab-pane fade" id="milestones" role="tabpanel" aria-labelledby="milestones-tab">
  @if($jejakLangkahs->isNotEmpty())
      @foreach($jejakLangkahs as $jejakLangkah)
          <img src="{{ asset('storage/' . $jejakLangkah->image) }}" alt="Jejak Langkah" class="img-fluid mb-3 gambare-jelang">
      @endforeach
  @else
      <img src="/assets/gambar_landingpage/jejak_ind.png" alt="Jejak Langkah" class="img-fluid mb-3 gambare-jelang">
  @endif
</div>
      <div class="tab-pane fade font-tentang-perusahaan" id="vision" role="tabpanel" aria-labelledby="vision-tab">
        @if($visi->isNotEmpty())
            <h2 style="margin-bottom: 15px; text-align: {{ $visi->first()->text_align }};"><b>VISI</b></h2>
            @foreach($visi as $visiItem)
                <p style="margin-bottom: 40px; text-align: {{ $visiItem->text_align }};">
                    {!! nl2br(e($visiItem->content['id'])) !!}
                </p>
            @endforeach
        @endif

        @if($misi->isNotEmpty())
            <h2 style="margin-bottom: 15px; text-align: {{ $misi->first()->text_align }};"><b>MISI</b></h2>
            @foreach($misi as $misiItem)
                <p style="font-size: large; text-align: {{ $misiItem->text_align }}; margin-bottom: 40px">
                    {!! nl2br(e($misiItem->content['id'])) !!}
                </p>
            @endforeach
        @endif

        @if($budaya->isNotEmpty())
            <h2 style="margin-bottom: 20px; text-align: {{ $budaya->first()->text_align }};">Budaya Perusahaan</h2>
            @foreach($budaya as $budayaItem)
                <h2 style="font-weight: bold; text-align: {{ $budaya->first()->text_align }};">SIAP BISA</h2>
                <p style="font-size: large; text-align: {{ $budayaItem->text_align }};">
                    {!! nl2br(e($budayaItem->content['id'])) !!}
                </p>
            @endforeach
        @endif

        <a href="{{ route('visimisi') }}" id="btn-tentang" role="button" class="btn btn-primary btn-lg"
            onmouseover="this.style.backgroundColor='#0d6efd';" 
            onmouseout="this.style.backgroundColor='#0454a3';">
            Baca Selengkapnya
        </a>
      </div>
        <!-- Sertifikat dan Penghargaan -->
      <div class="tab-pane fade" id="certificates" role="tabpanel" aria-labelledby="certificates-tab">
        <div id="certificatesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
          <div class="carousel-indicators" style="bottom: -80px; text-align: center;">
            @foreach($sertifikatPenghargaans as $index => $sertifikat)
                <button type="button" data-bs-target="#certificatesCarousel" data-bs-slide-to="{{ $index }}" 
                    class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : 'false' }}" 
                    aria-label="Slide {{ $index + 1 }}" 
                    style="width: 12px; height: 12px; border-radius: 50%; background-color: #0454a3; margin: 1px;">
                </button>
                @endforeach
        </div>
        <div class="carousel-inner">
          @foreach($sertifikatPenghargaans as $index => $sertifikat)
          <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
            <img id="sertifikat" src="{{ asset('storage/' . $sertifikat->image) }}" 
            class="d-block mx-auto" alt="Sertifikat {{ $index + 1 }}" style="">
          </div>
          @endforeach
        </div>
          <a href="{{ route('sertif') }}" role="button" class="btn btn-primary btn-lg" id="btn-tentang" style=""
             onmouseover="this.style.backgroundColor='#0d6efd';" onmouseout="this.style.backgroundColor='#0454a3';">
              Baca Selengkapnya
            </a>
            <!-- Kontrol Carousel -->
            <a class="carousel-control-prev" href="#certificatesCarousel" role="button" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true" id="carousel-control-sertif"></span>
            </a>
            <a class="carousel-control-next" href="#certificatesCarousel" role="button" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true" style="" id="carousel-control-sertif"></span>
            </a>
          </div>
        </div>
      </div>
    </div>
</section>
    
  <!--Foto Direksi-->
  <div class="container-fluid" style="padding: 0; margin: 0;">
    @if($fotoLayanan && $fotoLayanan->gambar_direksi_1)
        <img src="{{ asset('storage/' . $fotoLayanan->gambar_direksi_1) }}" class="img-fluid img-direksi" alt="Foto Layanan">
    @else
        <img src="/assets/gambar_landingpage/foto_direksi.jpeg" class="img-fluid img-direksi" alt="Default Gambar">
    @endif
</div>

<!--Konten Card Mengapa Memilih Kami-->
@include('beranda.partial-beranda.mpk')


<!--Video Youtube-->
<section style="background-color:rgba(236, 236, 236, 0.958); padding: 40px 0;">
  <div class="container" style="width: 100%; max-width: 100%; padding: 0; display: flex; justify-content: center;">
    <div class="embed-responsive embed-responsive-16by9" style="width: 75%; position: relative; padding-bottom: 42%; height: 0; overflow: hidden;">
      @php
        $defaultUrl = 'https://www.youtube.com/embed/f3bHlPrWspY';
        $youtubeUrl = $social->youtube_landing ?? '';
        
        // Extract video ID from various YouTube URL formats
        $videoId = '';
        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $youtubeUrl, $matches)) {
            $videoId = $matches[1];
        }
        
        $embedUrl = $videoId ? "https://www.youtube.com/embed/{$videoId}" : $defaultUrl;
      @endphp
      
      <iframe class="embed-responsive-item" 
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
        src="{{ $embedUrl }}" 
        allowfullscreen>
      </iframe>
    </div>
  </div>
</section>

<!-- Layanan Area -->
<section class="section-custom" style="padding: 0; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3), 0 6px 20px rgba(0, 0, 0, 0.19); position: relative; margin-bottom: 0;">
  @if($fotoLayanan && $fotoLayanan->jejak_langkah)
    <img class="img-fluid gambare-layanan" src="{{ asset('storage/' . $fotoLayanan->jejak_langkah) }}" alt="Layanan Area">
  @else
    <img class="img-fluid gambare-layanan" src="/assets/gambar_landingpage/layanan_area.png" alt="Default Layanan Area">
  @endif
  <a href="#" id="btn-selengkapnya-layanan" role="button"
     onmouseover="this.style.backgroundColor='#0d6efd';" onmouseout="this.style.backgroundColor='#0454a3';">
    Baca Selengkapnya
  </a>
</section>

<!-- Footer -->
@include('partial.footer')

{{-- Floating Button --}}
<div class="floating-btn" id="draggableBtn">
  <img src="/assets/gambar_landingpage/user.png" alt="Floating Button" class="btn-img">
  <h6 id="btn-hotline">HOTLINE</h6>

    <!-- Social Icons -->
    <div class="social-icons" style="position: absolute; left: -30px; top: 25%; transform: translateY(-50%);">
      <div class="icon facebook-icon" style="position: absolute; background-color: #0071BC; border-radius: 50%; width: 45px; height: 45px; display: flex; justify-content: center; align-items: center; transition: all 0.5s ease; opacity: 0;">
        <a href="https://www.facebook.com" target="_blank" style="color: white; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
          <i class="bi bi-facebook" style="font-size: 1.5rem;"></i>
        </a>
      </div>

      <div class="icon instagram-icon" style="position: absolute; background-color: #0071BC; border-radius: 50%; width: 45px; height: 45px; display: flex; justify-content: center; align-items: center; transition: all 0.5s ease; opacity: 0;">
        <a href="https://www.instagram.com" target="_blank" style="color: white; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
          <i class="bi bi-instagram" style="font-size: 1.5rem;"></i>
        </a>
      </div>

      <div class="icon youtube-icon" style="position: absolute; background-color: #0071BC; border-radius: 50%; width: 45px; height: 45px; display: flex; justify-content: center; align-items: center; transition: all 0.5s ease; opacity: 0;">
        <a href="https://www.youtube.com" target="_blank" style="color: white; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
          <i class="bi bi-youtube" style="font-size: 1.5rem;"></i>
        </a>
      </div>

      <div class="icon whatsapp-icon" style="position: absolute; background-color: #0071BC; border-radius: 50%; width: 45px; height: 45px; display: flex; justify-content: center; align-items: center; transition: all 0.5s ease; opacity: 0;">
        <a href="https://api.whatsapp.com/send?phone=6281281887873" target="_blank" style="color: white; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
          <i class="bi bi-whatsapp" style="font-size: 1.5rem;"></i>
        </a>
      </div>

      <div class="icon profile-icon" style="position: absolute; background-color: #0071BC; border-radius: 50%; width: 45px; height: 45px; display: flex; justify-content: center; align-items: center; transition: all 0.5s ease; opacity: 0;">
        <a href="{{ route('login') }}" style="color: white; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%;">
          <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
        </a>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/landingpage.js') }}"></script>
</body>
</html>


   


