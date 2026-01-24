# COMPANY PROFILE SWABINA GATRA

## Pembimbing lapang
- pak Wawan
- pak Aan

## ketua unit
-Ema kusnadi

## Tim Pengembang
- Vicky Fernando A.P.I / [https://github.com/Vckynando12](https://github.com/Vckynando12)
- Septiano Rizki / [https://github.com/SeptianoRizki](https://github.com/SeptianoRizki)
## Panduan Penggunaan

1. Instal dependensi dengan Composer.
    ```bash
    composer install
    ```

2. Salin file `.env.example` menjadi `.env` dan atur konfigurasi database.
    ```bash
    cp .env.example .env
    ```

3. Generate kunci aplikasi untuk `.env`.
    ```bash
    php artisan key:generate
    ```

4. Buat symbolic link untuk folder `storage`.
    ```bash
    php artisan storage:link
    ```

5. Jalankan migrasi database.
    ```bash
    php artisan migrate
    ```

6. Seed data ke dalam tabel `users` menggunakan seeder `UsersTableSeeder`.
    ```bash
    php artisan db:seed --class=UsersTableSeeder
    ```

 7. Seed data ke dalam tabel `users` menggunakan seeder `UsersTableSeeder`.
    ```bash
    composer require intervention/image
    ```
 8. Seed data ke dalam tabel `users` menggunakan seeder `UsersTableSeeder`.
    ```bash
    composer require stichoza/google-translate-php
    ```
9. Jalankan server lokal.
    ```bash
    php artisan serve
    ```

10. Buka aplikasi di browser dengan alamat yang muncul pada terminal, contoh URL: [http://localhost:8000](http://localhost:8000).

## Kontribusi

Kami menyambut kontribusi dari siapa saja yang ingin berpartisipasi dalam pengembangan proyek ini. Jika Anda ingin berkontribusi, silakan buat pull request dan kami akan meninjau kontribusi Anda.

Terima kasih telah berkontribusi pada proyek ini!
