<?php
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}
csrf_check();

if (isset($_SESSION['user_id'])) {
    clear_remember_cookie($_SESSION['user_id']);
}

// Hapus semua data session, cookie session, lalu hancurkan session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Mulai session baru hanya untuk menampilkan pesan sukses
session_start();
set_flash('success', 'Anda berhasil logout.');
redirect('login.php');
