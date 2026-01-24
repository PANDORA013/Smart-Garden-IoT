<!-- Carousel Swafm-->

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

<!-- Artikel Swafm -->
<h1 class="judul-swaac">SWA Facility Management</h1>
<section class="content-section">
    <!-- Gambar Pertama -->
    <div class="content-item align-left">
        @if($gambarFM->gambar1)
            <img src="{{ asset('storage/' . $gambarFM->gambar1) }}" alt="First Image" class="gambar1">
        @else
            <img src="{{ asset('path/to/placeholder-image.jpg') }}" alt="Placeholder" class="gambar1">
        @endif
        <p class="description">
            @php
                $firstText = $texts->where('id', 1)->first();
                $content = $firstText ? $firstText->content['en'] : 'Default text if no content found.';
                $content = str_replace("\r\n", "\n", $content);
            @endphp
            {!! nl2br(e($content)) !!}
        </p>
    </div>

    <!-- Gambar Kedua -->
    <div class="content-item align-right">
        @if($gambarFM->gambar2)
            <img src="{{ asset('storage/' . $gambarFM->gambar2) }}" alt="Second Image" class="gambar2">
        @else
            <img src="{{ asset('path/to/placeholder-image.jpg') }}" alt="Placeholder" class="gambar2">
        @endif
        
        @foreach($texts->where('id', '>=', 2) as $text)
            <p class="description">
                @php
                    $content = $text->content['en'] ?? 'Default text if no content found.';
                    $content = str_replace("\r\n", "\n", $content);
                @endphp
                {!! nl2br(e($content)) !!}
            </p>
        @endforeach
    </div>
</section>



