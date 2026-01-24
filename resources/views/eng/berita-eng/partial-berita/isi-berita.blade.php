<!-- Struktur Owl Carousel -->
<section class="panggen-berita">
    <h1 class="judule-berita">SWA News</h1>

    <div class="owl-carousel">
        @foreach($berita as $index => $item)
            <div class="item">
                <!-- Fancybox Preview -->
                <a href="{{ asset('storage/' . $item->image) }}" data-fancybox="gallery" data-caption="News {{ $index + 1 }}">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="News {{ $index + 1 }}">
                </a>
    
                <!-- Read More Button -->
                <button class="read-more-btn" data-target="desc{{ $index + 1 }}">Read More</button>
            </div>
        @endforeach
    </div>
    

    <!-- Navigation Buttons -->
    <div class="carousel-nav tombol-nav">
        <button class="prev-btn">
            <i class="bi bi-caret-left-fill"></i>
        </button>
        <button class="next-btn">
            <i class="bi bi-caret-right-fill"></i>
        </button>
    </div>
</section>

<section class="deskripsi-berita">
    @foreach($berita as $index => $item)
        <div id="desc{{ $index + 1 }}" class="description">
            <h3 class="sub-judul">
                {{ isset($item->title['en']) ? $item->title['en'] : '' }}
            </h3>
            <p class="pengaturan-font-deskripsi">
                {!! nl2br(e(isset($item->description['en']) ? $item->description['en'] : '')) !!}
            </p>
        </div>
    @endforeach
</section>