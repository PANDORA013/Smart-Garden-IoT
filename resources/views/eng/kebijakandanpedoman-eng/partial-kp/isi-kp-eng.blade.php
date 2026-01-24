<!-- Tambahkan CSS Fancybox di bagian head -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />

<section class="panggen-kp container">
    <!-- Judul Section -->
    <div class="judul-wrapper text-center my-4">
        <h1 class="judule-kp">Policy and Guideline</h1>
    </div>

    <!-- Carousel Konten -->
    <div class="carousel-wrapper position-relative">
        <div class="owl-carousel">
            @foreach($pedomans as $pedoman)
            <div class="item text-center">
                <div class="card">
                    <div class="card-body card-kebijakan">
                        <h5>{{ $pedoman->judul }}</h5>
                        @if($pedoman->gambar)
                            <img src="{{ asset('storage/images/' . $pedoman->gambar) }}" 
                                 alt="{{ $pedoman->judul }}" 
                                 class="img-fluid gambar-kebijakan">
                        @endif
                        @if($pedoman->file)
                            <a href="{{ asset('storage/documents/' . $pedoman->file) }}" 
                               data-fancybox="pdf"
                               data-width="100%"
                               data-height="100%"
                               class="btn btn-primary mt-3">
                                See Detail
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tombol Previous dan Next -->
        <div class="carousel-nav position-absolute w-100 d-flex justify-content-between px-3">
            <button class="prev-btn btn btn-outline-primary">
                <i class="bi bi-caret-left-fill"></i>
            </button>
            <button class="next-btn btn btn-outline-primary">
                <i class="bi bi-caret-right-fill"></i>
            </button>
        </div>
    </div>
</section>

<!-- Tambahkan JavaScript Fancybox sebelum closing </body> -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
    Fancybox.bind('[data-fancybox="pdf"]', {
        defaultDisplay: 'pdf',
        dragToClose: false,
        toolbar: {
            display: {
                left: [
                    "infobar",
                ],
                middle: [
                    "zoomIn",
                    "zoomOut",
                    "toggle1to1",
                    "rotateCCW",
                    "rotateCW",
                    "flipX",
                    "flipY",
                ],
                right: [
                    "close",
                ],
            },
        },
    });
</script>

