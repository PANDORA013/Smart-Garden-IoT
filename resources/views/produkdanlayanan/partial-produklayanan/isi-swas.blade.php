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
<h1 class="judul-swaac">SWA Segar</h1>
<section class="content-section">
    <!-- Gambar Pertama -->
    <div class="content-item align-left">
        @if($gambarSS && $gambarSS->gambar1)
            <img src="{{ asset('storage/' . $gambarSS->gambar1) }}" alt="Gambar Pertama" class="gambar1">
        @else
            <p>Ganbar tidak ditemukan</p>
        @endif
        <p class="description" style="text-align: {{ $textss->where('id', 1)->first()->text_align ?? 'left' }};">
            @if($textss->where('id', 1)->first())
                {!! nl2br(e($textss->where('id', 1)->first()->content['id'])) !!}
            @else
                Tidak ada data ditemukan pada id 1                
            @endif
        </p>
    </div>
    <!-- Gambar Kedua -->
    <div class="content-item align-right">
        @if($gambarSS && $gambarSS->gambar2)
            <img src="{{ asset('storage/' . $gambarSS->gambar2) }}" alt="Gambar Kedua" class="gambar2">
        @else
            <p>Gambar tidak ditemukan</p>
        @endif
        @foreach($textss->where('id', '>', 1) as $text)
            <p class="description" style="text-align: {{ $text->text_align }};">
                {!! nl2br(e($text->content['id'])) !!}
            </p>
        @endforeach
    </div>
</section>



