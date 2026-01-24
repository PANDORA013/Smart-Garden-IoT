@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Admin Dashboard</h1>
</div>
<div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Berita</p>
                    <h2 class="font-weight-bolder text-center">
                      {{ $beritas->count() }}
                    </h2>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder">+Aktif</span>
                      Saat Ini
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                    <i class="fas fa-newspaper text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">    
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers ">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold"> Sertifikat</p>
                                <h2 class="font-weight-bolder text-center">
                                    {{ $sertifikats->count() }}
                                </h2>
                                <p class="mb-0">
                                    <span class="text-success text-sm font-weight-bolder">+Aktif</span>
                                    Saat Ini
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                <i class="fas fa-certificate text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">FAQ</p>
                    <h2 class="font-weight-bolder text-center">
                      {{ $faqs->count() }}
                    </h2>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder">+Aktif</span>
                      Saat Ini
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                    <i class="fas fa-question-circle text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- New Karir Card -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pedoman</p>
                    <h2 class="font-weight-bolder text-center">
                      {{ $pedomans->count() }}
                    </h2>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder">+Aktif</span>
                      Saat Ini
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                    <i class="fas fa-book text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-5">
        <!-- Chart Column -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-body p-3">
                    <h5 class="mb-3">Statistik Data</h5>
                    <div class="chart-container d-flex align-items-end">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carousel Berita Column -->
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-body p-3">
                    <h5 class="mb-3">Berita Terkini</h5>
                    <div id="beritaCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            @foreach($beritas as $index => $berita)
                                <button type="button" data-bs-target="#beritaCarousel" data-bs-slide-to="{{ $index }}" 
                                    class="{{ $index == 0 ? 'active' : '' }}" aria-current="true" 
                                    aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                        <div class="carousel-inner">
                            @foreach($beritas as $index => $berita)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <div class="d-flex justify-content-center">
                                        <div style="width: 100%; aspect-ratio: 1/1; border-radius: 15px; overflow: hidden;">
                                            <img src="{{ asset('storage/' . $berita->image) }}" 
                                                class="d-block w-100 h-100" 
                                                style="object-fit: cover;"
                                                alt="Berita {{ $index + 1 }}">
                                        </div>
                                    </div>
                                    <div class="carousel-caption d-none d-md-block">
                                        <h6 class="text-white mb-1">{{ $berita->title[app()->getLocale()] }}</h6>
                                        <p class="small">{{ Str::limit($berita->description[app()->getLocale()], 50) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#beritaCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#beritaCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <!-- Moved Sertifikat Carousel here -->
      <div class="row mt-4">
          <!-- Combined Info Card -->
          <div class="col-12 col-lg-5">
              <div class="card h-100">
                  <div class="card-body p-4">
                      <!-- Produk & Layanan Section -->
                      <h5 class="mb-3">Produk & Layanan</h5>
                      <ul class="list-group list-group-flush">
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-building me-3 text-primary"></i>
                              SWA Facility Management
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-leaf me-3 text-success"></i>
                              SWA Segar
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-calendar-alt me-3 text-info"></i>
                              SWA Tour & Event Organizer
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-laptop-code me-3 text-warning"></i>
                              SWA Digital Solution
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-graduation-cap me-3 text-danger"></i>
                              SWA Academy
                          </li>
                      </ul>

                      <!-- Divider -->
                      <hr class="my-4">

                      <!-- Tentang Kami Section -->
                      <h5 class="mb-3">Tentang Kami</h5>
                      <ul class="list-group list-group-flush">
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-building-columns me-3 text-primary"></i>
                              Sekilas Perusahaan
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-bullseye me-3 text-success"></i>
                              Visi Misi & Budaya
                          </li>
                          <li class="list-group-item d-flex align-items-center">
                              <i class="fas fa-award me-3 text-warning"></i>
                              Sertifikat & Penghargaan
                          </li>
                      </ul>
                  </div>
              </div>
          </div>

          <!-- Sertifikat Carousel -->
          <div class="col-12 col-lg-7">
              <div class="card h-100">
                  <div class="card-body p-3">
                      <h5 class="mb-3">Sertifikat Terkini</h5>
                      <div id="sertifikatCarousel" class="carousel slide" data-bs-ride="carousel">
                          <div class="carousel-indicators">
                              @foreach($sertifikats as $index => $sertifikat)
                                  <button type="button" data-bs-target="#sertifikatCarousel" data-bs-slide-to="{{ $index }}" 
                                      class="{{ $index == 0 ? 'active' : '' }}" aria-current="true" 
                                      aria-label="Slide {{ $index + 1 }}"></button>
                              @endforeach
                          </div>
                          <div class="carousel-inner">
                              @forelse($sertifikats as $index => $sertifikat)
                                  <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                      <div class="d-flex justify-content-center">
                                          <div style="width: 100%; aspect-ratio: 1/1; border-radius: 15px; overflow: hidden;">
                                              @if($sertifikat->image)
                                                  <img src="{{ asset('storage/' . $sertifikat->image) }}" 
                                                      class="d-block w-100 h-100" 
                                                      style="object-fit: cover;"
                                                      alt="Sertifikat {{ $index + 1 }}">
                                              @endif
                                          </div>
                                      </div>
                                      
                                  </div>
                              @empty
                                  <div class="carousel-item active">
                                      <div class="d-flex justify-content-center align-items-center" style="height: 250px;">
                                          <p>Tidak ada sertifikat tersedia</p>
                                      </div>
                                  </div>
                              @endforelse
                          </div>
                          <button class="carousel-control-prev" type="button" data-bs-target="#sertifikatCarousel" data-bs-slide="prev">
                              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                              <span class="visually-hidden">Previous</span>
                          </button>
                          <button class="carousel-control-next" type="button" data-bs-target="#sertifikatCarousel" data-bs-slide="next">
                              <span class="carousel-control-next-icon" aria-hidden="true"></span>
                              <span class="visually-hidden">Next</span>
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>
</div>

@push('scripts')
<script>
    const ctx = document.getElementById('myChart');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Berita', 'Sertifikat', 'FAQ', 'Pedoman'],
            datasets: [{
                label: 'Jumlah Data',
                data: [{{ $beritas->count() }}, {{ $sertifikats->count() }}, {{ $faqs->count() }}, {{ $pedomans->count() }}],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1,
                borderRadius: 10,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear',
                    from: 1,
                    to: 0,
                    loop: false
                },
                y: {
                    duration: 2000,
                    delay: (context) => context.dataIndex * 300
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: '',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
</script>

<style>
    #myChart {
        height: 400px !important;
        max-width: 100%;
        margin: 0 auto;
    }
    
    .chart-container {
        height: 400px;
        position: relative;
        width: 100%;
        overflow-y: auto;
    }

    .carousel-item img {
        height: 250px;
        object-fit: cover;
    }

    .card {
        margin-bottom: 0;
    }

    .carousel-caption {
        background: rgba(0, 0, 0, 0.5);
        padding: 8px;
        border-radius: 5px;
        bottom: 15px;
    }

    .chart-container::-webkit-scrollbar {
        width: 6px;
    }

    .chart-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .chart-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .chart-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .list-group-item {
        border: none;
        padding: 1rem 0.5rem;
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(10px);
    }

    .list-group-item i {
        width: 24px;
        text-align: center;
    }
</style>
@endpush

@endsection


