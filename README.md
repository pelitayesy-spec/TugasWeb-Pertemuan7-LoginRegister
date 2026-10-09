# Tugas Rutin 7 - Sistem Login/Register (PHP Native)

Sistem login/register sederhana dengan PHP Native dan penyimpanan file JSON.

## Fitur
- Registrasi dengan validasi (nama, email, password + konfirmasi)
- Validasi email dengan `filter_var()`
- Password di-hash dengan `password_hash()` dan diverifikasi dengan `password_verify()`
- Data tersimpan di `data/users.json`
- Cek duplikat email saat registrasi
- Login dengan session (`session_regenerate_id()` setelah login)
- Dashboard terproteksi (redirect ke login jika belum login)
- Logout dengan `session_destroy()`
- Sanitasi output dengan `htmlspecialchars()`
- Pesan error dan sukses yang jelas
- Bonus: Remember Me (cookie), edit profil, tampilan CSS, proteksi CSRF

## Cara Menjalankan
Butuh PHP 8.1 atau lebih baru.

```bash
php -S localhost:8000
```

Buka http://localhost:8000

## Struktur
```
index.php  register.php  login.php  dashboard.php  edit_profile.php  logout.php
includes/  functions.php  header.php  footer.php
assets/    style.css
data/      users.json
```
