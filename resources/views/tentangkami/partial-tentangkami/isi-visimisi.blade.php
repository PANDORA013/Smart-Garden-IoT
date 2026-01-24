<section class="section-vmb">
    <div class="bagian-visimisi">
        @if($visiMisiBudaya->where('type', 'visi')->isNotEmpty())
            @php
                $visiAlignment = $visiMisiBudaya->where('type', 'visi')->first()->text_align;
            @endphp
            <h1 class="judul-visi" style="text-align: {{ $visiAlignment }};">VISI</h1>
            @foreach($visiMisiBudaya->where('type', 'visi') as $visi)
                <p class="teks-vmb" style="text-align: {{ $visi->text_align }};">
                    {!! nl2br(e($visi->content['id'])) !!}
                </p>
            @endforeach
        @else
            <h1 class="judul-visi">VISI</h1>
            <p class="teks-vmb">Data visi belum ditambahkan</p>
        @endif
    </div>

    <div class="bagian-visimisi">
        @if($visiMisiBudaya->where('type', 'misi')->isNotEmpty())
            @php
                $misiAlignment = $visiMisiBudaya->where('type', 'misi')->first()->text_align;
            @endphp
            <h1 class="judul-misi" style="text-align: {{ $misiAlignment }};">MISI</h1>
            @foreach($visiMisiBudaya->where('type', 'misi') as $misi)
                <p class="teks-vmb" style="text-align: {{ $misi->text_align }};">
                    {!! nl2br(e($misi->content['id'])) !!}
                </p>
            @endforeach
        @else
            <h1 class="judul-misi">MISI</h1>
            <p class="teks-vmb">Data misi belum ditambahkan</p>
        @endif
    </div>

    <div class="bagian-budaya">
        @if($visiMisiBudaya->where('type', 'budaya')->isNotEmpty())
            @php
                $budayaAlignment = $visiMisiBudaya->where('type', 'budaya')->first()->text_align;
            @endphp
            <h1 class="judul-budaya" style="text-align: {{ $budayaAlignment }};">BUDAYA</h1>
            <h3 class="subjudul-siapbisa" style="text-align: {{ $budayaAlignment }};">
                <b>SIAP BISA</b>
            </h3>
            @foreach($visiMisiBudaya->where('type', 'budaya') as $budaya)
                <p class="teks-vmb" style="text-align: {{ $budaya->text_align }};">
                    {!! nl2br(e($budaya->content['id'])) !!}
                </p>
            @endforeach
        @else
            <h1 class="judul-budaya">BUDAYA</h1>
            <h3 class="subjudul-siapbisa"><b>SIAP BISA</b></h3>
            <p class="teks-vmb">Data budaya belum ditambahkan</p>
        @endif
    </div>
</section>