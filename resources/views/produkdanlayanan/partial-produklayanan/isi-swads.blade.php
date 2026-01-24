<!-- Carousel Swaac-->

<section>
    <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-pause="false" style="position: relative;">
        <div class="carousel-inner">
            @foreach($carousels as $index => $carousel)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <img src="{{ asset('storage/' . $carousel->image) }}" class="d-block w-100 carousel-image" alt="Slide {{ $index + 1 }}">
                </div>
            @endforeach
        </div>
    
        <div class="carousel-controls" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 1000;">
            @foreach($carousels as $index => $carousel)
                <span class="dot" data-bs-slide-to="{{ $index }}" style="width: 12px; height: 12px; background-color: white; border-radius: 50%; display: inline-block; transition: background-color 0.3s ease;"></span>
            @endforeach
        </div>
    </div>
  </section>

<!-- Artikel Swaac -->
<h1 class="judul-swaac">SWA Tech</h1>
<section class="content-section">
    <!-- Gambar Pertama -->
    <div class="content-item align-left">
        @if($gambards && $gambards->gambar1)
            <img src="{{ asset('storage/' . $gambards->gambar1) }}" alt="Gambar Pertama" class="gambar1">
        @else
            <p>Gambar pertama belum tersedia.</p>
        @endif
        @if($texts->isNotEmpty())
            <p class="description" style="text-align: {{ $texts->first()->text_align }};">
                {!! nl2br(e($texts->first()->content['id'])) !!}
            </p>
        @else
            <p class="description">Konten belum tersedia.</p>
        @endif
    </div>
    <!-- Gambar Kedua -->
    <div class="content-item align-right">
        @if($gambards && $gambards->gambar2)
            <img src="{{ asset('storage/' . $gambards->gambar2) }}" alt="Gambar Kedua" class="gambar2">
        @else
            <p>Gambar kedua belum tersedia.</p>
        @endif
        @foreach($texts->skip(1) as $text)
            <p class="description" style="text-align: {{ $text->text_align }};">
                {!! nl2br(e($text->content['id'])) !!}
            </p>
        @endforeach
    </div>
</section>




