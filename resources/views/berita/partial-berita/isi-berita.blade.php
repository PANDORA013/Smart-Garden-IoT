<!-- Struktur Owl Carousel -->
<section class="panggen-berita">
    <h1 class="judule-berita">Berita SWA</h1>

    <!-- Tambahkan tombol toggle bahasa -->
    

    <div class="owl-carousel">
        @foreach($berita as $index => $item)
            <div class="item">
                <!-- Fancybox Preview -->
                <a href="{{ asset('storage/' . $item->image) }}" data-fancybox="gallery" data-caption="Berita {{ isset($item->title['id']) ? $item->title['id'] : '' }}">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="Berita {{ $index + 1 }}">
                </a>
    
                <!-- Read More Button -->
                <button class="read-more-btn" data-target="desc{{ $index + 1 }}">
                    <span class="content-id">Baca Selengkapnya</span>
                    <span class="content-en" style="display: none;">Read More</span>
                </button>
            </div>
        @endforeach
    </div>
    

    <!-- Tombol Previous dan Next -->
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
                <span class="content-id">{{ isset($item->title['id']) ? $item->title['id'] : '' }}</span>
            </h3>
            <p class="pengaturan-font-deskripsi">
                <span class="content-id">{!! nl2br(e(isset($item->description['id']) ? $item->description['id'] : '')) !!}</span>
            </p>
        </div>
    @endforeach
</section>

<script>
function toggleLanguage(lang) {
    // Update active state of buttons
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    // Toggle content visibility
    if (lang === 'id') {
        document.querySelectorAll('.content-id').forEach(el => el.style.display = '');
    } else {
        document.querySelectorAll('.content-id').forEach(el => el.style.display = 'none');
    
    }
}
</script>