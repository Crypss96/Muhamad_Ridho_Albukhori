# Form Data Mahasiswa — PHP Array

Aplikasi sederhana satu file PHP untuk menerima data mahasiswa dari formulir, menyimpannya ke array pada session PHP, dan menampilkannya dalam tabel HTML.

## Fitur

- Form input nama, NIM, program studi, dan email.
- Validasi sisi server untuk data wajib dan format email.
- Data ditampilkan dengan `foreach` dari array sesi PHP.
- Escape output HTML untuk mengurangi risiko XSS.
- Token CSRF dan pola Post/Redirect/Get.
- Layout responsif dengan CSS yang ditulis langsung di `index.php`.
- Tombol untuk mengosongkan data sesi.

## Persyaratan

- PHP 8.1 atau lebih baru (menggunakan `mixed` dan return type `never`).
- Tidak memerlukan database, framework, Composer, atau JavaScript.

## Cara menjalankan

Dari folder `form-array-php`, jalankan server bawaan PHP:

```bash
php -S localhost:8000
```

Kemudian buka `http://localhost:8000` di browser.

Alternatifnya, letakkan folder ini di direktori web server yang mendukung PHP, misalnya `htdocs` pada XAMPP, lalu buka `http://localhost/form-array-php/`.

## Catatan penyimpanan

Data disimpan di `$_SESSION['students']`, bukan database atau file permanen. Data biasanya hanya tersedia selama sesi PHP yang sama. Ini cocok untuk latihan dasar PHP array; untuk aplikasi nyata yang perlu menyimpan data permanen, gunakan database dan autentikasi/otorisasi yang sesuai.
