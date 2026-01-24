<!-- Carousel Kontak -->
<section class="panggen-carousel">
    <div id="carouselExampleFade" class="carousel slide carousel-fade">
        <div class="carousel-inner">
            @foreach($carousels as $key => $carousel)
            <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                <img src="{{ asset('storage/' . $carousel->image) }}" class="d-block carousel-kontak" alt="Carousel Image">
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="bagian-kontak">
    <div class="container">
        <div class="row">
          <h1 class="judul-kontak">Kontak</h1>
          <div class="col-sm-5 col-md-6"><br>
            @foreach($textKK as $text)
            @if($text->content)
            <p class="desk-pusat" style="text-align: {{ $text->text_align }}">
                {!! nl2br($text->content) !!}
                <br>
                <a class="btn btn-primary tombol-pusat" href="{{ $text->link }}" role="button">Lokasi</a>
            </p><br>  
            @endif
            @endforeach
          </div>
          <div class="col-sm-5 offset-sm-2 col-md-6 offset-md-0">
            @foreach($gambarKK as $gambar)
            <img src="{{ asset('storage/' . $gambar->image) }}" class="img-fluid gambar-kontak" alt="Office Image">
            @endforeach
          </div>
        </div>
    </div>
</section>

<section class="bagian-tanyakami">
  <div class="container">
    <div class="row">
    <div class="col-lg-12">
    <h1 class="judul-tanyakami">Tanya Kami</h1>
    <p class="subteks-judul-tanyakami">Anda punya pertanyaan seputar produk dan layanan SWA, silahkan sampaikan melalui formular berikut ini:</p>
    <form>
        <div class="row mb-3">
          <label for="inputname" class="col-sm-2 col-form-label">Nama</label>
          <div class="col kolom-isian">
            <input type="text" class="form-control" placeholder="Isikan Nama Anda" aria-label="Nama">
          </div>
        </div>
        <div class="row mb-3">
            <label for="inputname" class="col-sm-2 col-form-label">Perusahaan/Instansi</label>
            <div class="col kolom-isian">
              <input type="text" class="form-control" placeholder="Isikan Nama Perusahaan Anda" aria-label="Perusahaan">
            </div>
          </div>
          <div class="row mb-3">
            <label for="inputname" class="col-sm-2 col-form-label">Alamat</label>
            <div class="col kolom-isian">
              <input type="text" class="form-control" placeholder="Isikan Alamat Anda" aria-label="Alamat">
            </div>
          </div>
          <div class="row mb-3">
            <label for="inputname" class="col-sm-2 col-form-label">Email</label>
            <div class="col kolom-isian">
              <input type="text" class="form-control" placeholder="Isikan Email Anda" aria-label="Email">
            </div>
          </div>
          <div class="row mb-3">
            <label for="inputname" class="col-sm-2 col-form-label">Subjek Pesan</label>
            <div class="col kolom-isian">
              <input type="text" class="form-control" placeholder="Isikan Subjek Pesan Anda" aria-label="Subjek Pesan">
            </div>
          </div>
          <h1 class="judul-isipesan">Isi Pesan</h1>
          <div class="form-floating">
            <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px"></textarea>
            <label for="floatingTextarea2">Pesan</label>
          </div>
          <div class="form-group align-left mt-3">
            <button type="submit" class="btn btn-default morebtn more black con-submit tombol-submit">Kirim</button>
          </div>
      </form>
    </div>
    </div>
  </div>
</section>

<section class="bagian-faq">
  <div class="container mt-5">
    <h1 class="judul-faq">FAQ</h1>
    @foreach($faqs as $faq)
      <!-- Pertanyaan -->
      <div class="d-flex mb-3 align-items-start" style="justify-content: {{ $faq->text_align === 'center' ? 'center' : ($faq->text_align === 'right' ? 'flex-end' : 'flex-start') }}">
        <div class="icon" style="margin-{{ $faq->text_align === 'right' ? 'left' : 'right' }}: 10px;">
          <i class="bi bi-person"></i>
        </div>
        <p class="question mb-0" style="text-align: {{ $faq->text_align }}">
          {{ $faq->getPertanyaan('id') }}
        </p>
      </div>
      
      <!-- Jawaban -->
      <div class="d-flex mb-5 align-items-start" style="justify-content: {{ $faq->text_align === 'center' ? 'center' : ($faq->text_align === 'right' ? 'flex-end' : 'flex-start') }}">
        <div class="icon" style="margin-{{ $faq->text_align === 'right' ? 'left' : 'right' }}: 10px;">
          <i class="bi bi-person-fill"></i>
        </div>
        <p class="answer mb-0" style="text-align: {{ $faq->text_align }}">
          {!! $faq->getJawaban('id') !!}
        </p>
      </div>
    @endforeach
  </div>
</section>

