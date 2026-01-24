<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PT Swabina Gatra Official Website</title>
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/css/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/owl.theme.default.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/karir.css')}}">
    <style>
        .karir-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        
        .karir-item:last-child {
            border-bottom: none;
        }
        
        .file-icon {
            margin-left: 15px;
        }
        
        .file-icon i {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    @include ('partial.header')

    <!-- Navigasi -->
    @include ('partial.navigasi')

    <!-- Iki isine sekilas -->
    @include ('karir.partial-karir.isi-karir')

    <!-- Footer -->
    @include ('partial.footer')

    <!-- Floating Button-->
    @include ('partial.floating')

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/karir.js') }}"></script>

</body>
</html>