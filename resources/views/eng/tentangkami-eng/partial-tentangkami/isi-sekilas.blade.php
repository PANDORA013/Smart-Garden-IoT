<!-- Carousel Sekilas -->

<section>
    <h1 class="judul-sekilas">Company Overview</h1>
    <div id="carouselExampleFade" class="carousel slide carousel-fade">
        <div class="carousel-inner">
            @if($fotolayanan->isNotEmpty())
                @php
                    $fotoLayanan = $fotolayanan->first();
                @endphp
                @if($fotoLayanan->gambar_direksi_1)
                    <div class="carousel-item active">
                        <img src="{{ asset('storage/' . $fotoLayanan->gambar_direksi_1) }}" class="d-block carousel-sekilas" alt="Foto Direksi 1">
                    </div>
                @endif
                @if($fotoLayanan->gambar_direksi_2)
                    <div class="carousel-item {{ !$fotoLayanan->gambar_direksi_1 ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . $fotoLayanan->gambar_direksi_2) }}" class="d-block carousel-sekilas" alt="Foto Direksi 2">
                    </div>
                @endif
            @endif
            @if(!$fotolayanan->isNotEmpty() || (!$fotoLayanan->gambar_direksi_1 && !$fotoLayanan->gambar_direksi_2))
                <div class="carousel-item active">
                    <img src="/assets/gambar-sekilas/slide1.jpeg" class="d-block carousel-sekilas" alt="Default Slide 1">
                </div>
                <div class="carousel-item">
                    <img src="/assets/gambar-sekilas/slide2.jpg" class="d-block carousel-sekilas" alt="Default Slide 2">
                </div>
            @endif
        </div>
        <!-- Tombol panah kiri -->
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <!-- Tombol panah kanan -->
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleFade" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    
    <div class="deskripsi-sekilas">
        @if($sekilas->isNotEmpty())
        @foreach($sekilas as $sekilasItem)
        <p class="font-tentang-perusahaan" style="text-align: {{ $sekilasItem->text_align }};">
            {!! nl2br(e($sekilasItem->maintext['en'])) !!}
        </p>
    @endforeach
        @else
            <h3>No data available</h3>
        @endif
    </div>
</section>
