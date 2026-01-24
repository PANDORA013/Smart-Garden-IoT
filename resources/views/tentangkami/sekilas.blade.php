<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PT Swabina Gatra Official Website</title>
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/tentangkami.css')}}">
</head>
<body>
    <!-- Header -->
    @include('partial.header')

    <!-- Navigasi -->
    @include('partial.navigasi')

    <!-- Konten Sekilas -->
    <main>
        <!-- Iki isine sekilas -->
        @include('tentangkami.partial-tentangkami.isi-sekilas')

        <!-- Tambahan konten spesifik untuk halaman sekilas bisa ditambahkan di sini -->
    </main>

    <!-- Footer -->
    @include('partial.footer')

    <!-- Floating Button-->
    @include('partial.floating')

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/tentangkami.js') }}"></script>
</body>
</html>
