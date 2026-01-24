<section class="isi-konten-mengapa">
<div class="container">
    <h1 class="text-center judul-mengapa">Why Choose Us</h1>
    <div class="why-choose-us-btn-container">
        @foreach($MK as $index => $item)
            <button class="btn why-choose-us-btn {{ $index == 0 ? 'active' : '' }}" data-target="#mk-{{ $item->id }}">
                {{ is_array($item->title) ? $item->title['en'] : $item->title }}
            </button>
        @endforeach
    </div>

    @foreach($MK as $index => $item)
        <div id="mk-{{ $item->id }}" class="why-choose-us-content-section {{ $index == 0 ? 'active' : '' }}">
            <img src="{{ asset('storage/' . $item->image) }}" class="img-fluid gambare-konten" alt="{{ is_array($item->title) ? $item->title['en'] : $item->title }} Image">
            <h1 class="judule-konten">{{ is_array($item->title) ? $item->title['en'] : $item->title }}</h1>
            <p class="deskripsine-konten">
                {{ is_array($item->description) ? $item->description['en'] : $item->description }}
            </p>
        </div>
    @endforeach
</div>
</section>

<section class="bg-section-mengapa">
    <div class="carousel-wrapper">
        <div id="carouselContoh" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="d-flex justify-content-center">
                        <div class="card card-mengapa">
                            <img src="/assets/gambar_mengapa/bs1.png" class="card-img-top img-fluid" alt="Gambar 1">
                            <div class="card-body">
                                <h2 class="card-title judul-card">Religi dan Nasionalis</h2>
                                <p class="card-text deskripsi-card">Religi = Melaksanakan ibadah keagamaan yang dianut.<br><br>Nasionalis = mampu secara spontanitas menyanyikan lagu Indonesia Raya & lafadz Pancasila</p>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="carousel-item">
                    <div class="d-flex justify-content-center">
                        <div class="card card-mengapa">
                            <img src="/assets/gambar_mengapa/bs2.png" class="card-img-top img-fluid" alt="Gambar 2">
                            <div class="card-body">
                                <h2 class="card-title judul-card">Budaya Swabina Gatra</h2>
                                <p class="card-text deskripsi-card">
                                    1. <b>Visi</b> Swabina Gatra = "Menjadi Perusahaan Yang Dapat Tumbuh dan Berkembang dengan <b>SEHAT</b> dan Selalu <b>UNGGUL</b> dibidangnya"<br>
                                    2. Makna <b>SIAP</b> = Semangat, Ikhlas, Akhlak, Profesional<br>
                                    3. Makna <b>BISA</b> adalah dengan Rahmat ALLAH SWT PT. Swabina Gatra pasti <b>BISA</b> Mewujudkan Visi Perusahaan saat ini dan dimasa selanjutnya<br>
                                    4. <b>Spirit Swabina Gatra</b> New Face yaitu<br>
                                    a. Siapa Kita = Swabina Gatra<br>
                                    b. Swabina = Siap Bisa<br>
                                    c. Swabina Gatra = New Face
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <div class="card card-mengapa">
                        <img src="/assets/gambar_mengapa/bs3.png" class="card-img-top img-fluid" alt="Gambar 3">
                        <div class="card-body">
                            <h2 class="card-title judul-card">Budaya K3L</h2>
                            <p class="card-text deskripsi-card">Melaksanakan aktivitas kerja sesuai ISO 45001:2018 Sistem Manajemen Keselamatan Kesehatan Kerja (SMK3L) dan ISO 14001:2015 Sistem Manajemen Lingkugan (SML)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <div class="card card-mengapa">
                        <img src="/assets/gambar_mengapa/bs4.png" class="card-img-top img-fluid" alt="Gambar 4">
                        <div class="card-body">
                            <h2 class="card-title judul-card">Budaya 5R</h2>
                            <p class="card-text deskripsi-card">
                                1. 5R = Ringkas, Rapi, Resik, Rawat, Rajin<br>2. <b>Ringkas</b> artinya menyingkirkan barang-barang yang tidak 
                                diperlukan.<br>3. <b>Rapi</b> artinya meletakkan sesuai posisi yang telah ditetapkan.<br>4. <b>Resik</b> artinya membersihkan 
                                peralatan dan daerah kerja<br>5. <b>Rawat</b> artinya menjaga kebersihan pribadi & tetap melaksanakan 3R sebelumnya<br>
                                6. <b>Rajin</b> artinya memelihara disiplin pribadi (komitmen) dalam menjalankan seluruh tahapan 5R</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="d-flex justify-content-center">
                    <div class="card card-mengapa">
                        <img src="/assets/gambar_mengapa/bs5.png" class="card-img-top img-fluid" alt="Gambar 5">
                        <div class="card-body">
                            <h2 class="card-title judul-card">Service Excellence</h2>
                            <p class="card-text deskripsi-card">1. <b>Service Excellence (Pelayanan Prima)</b> adalah suatu pelayanan terbaik dan memenuhi harapan dan kebutuhan pelanggan/user<br>2. <b>Unsur - Unsur <i> Service Excellence</i></b> yaitu<br>a. Attitude (Estetika, Etika, Etos)<br>b. SOP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselContoh" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselContoh" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Selanjutnya</span>
        </button>
    </div>
</div>
</section>

