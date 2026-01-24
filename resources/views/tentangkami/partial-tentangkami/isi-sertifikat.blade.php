<section class="bag-sertif">
  <h1 class="judule-sertif">Sertifikat dan Penghargaan</h1>
  <div id="carouselExampleIndicators" class="carousel slide bagian-sertif" data-bs-ride="carousel" data-bs-interval="2000">
    <div class="carousel-indicators">
      @foreach($sertifikatPenghargaan as $index => $sertifikat)
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $index }}" 
                class="sertifikat-dot {{ $index == 0 ? 'active' : '' }}" 
                aria-current="{{ $index == 0 ? 'true' : 'false' }}" 
                aria-label="Slide {{ $index + 1 }}"></button>
      @endforeach
    </div>

    <div class="carousel-inner">
      @foreach($sertifikatPenghargaan as $index => $sertifikat)
        <div class="carousel-item {{ $index == 0 ? 'active' : '' }} ini-sertif">
          <img src="{{ asset('storage/' . $sertifikat->image) }}" class="d-block w-100" alt="sertifikat{{ $index + 1 }}">
        </div>
      @endforeach
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
      <i class="bi bi-triangle-fill left-arrow" aria-hidden="true"></i>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
      <i class="bi bi-triangle-fill right-arrow" aria-hidden="true"></i>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
</section>

<section>
  <p class="deskripsi-sertifikat">Kepercayaan Pelanggan merupakan hal yang paling utama bagi SWA. Karena itulah SWA selalu menjaganya melalui 
    penyediaan produk dan layanan yang berkualitas. Usaha terbaik SWA dalam meraih standar mutu tertinggi demi kepuasan pelanggan, 
    telah diakui dengan adanya penghargaan dan pencapaian yang bergengsi. Kedepannya, SWA akan terus berupaya untuk terus 
    meningkatkan kualitas produk dan layanan yang dihasilkan, melalui berbagai inovasi tiada henti diikuti dengan 
    pengawasan mutu yang ketat.</p>
</section>

<section class="penghargaan">
  <img src="/assets/gambar-sertifikat/peng1.png" class="gambar-penghargaan" alt="penghargaan1">
  <img src="/assets/gambar-sertifikat/peng2.png" class="gambar-penghargaan" alt="penghargaan2">
  <img src="/assets/gambar-sertifikat/peng3.png" class="gambar-penghargaan" alt="penghargaan3">
  <img src="/assets/gambar-sertifikat/peng4.png" class="gambar-penghargaan" alt="penghargaan4">
  <img src="/assets/gambar-sertifikat/peng5.png" class="gambar-penghargaan" alt="penghargaan5">
</section>