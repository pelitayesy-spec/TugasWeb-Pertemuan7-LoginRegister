<?php
require __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$nama   = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validasi nama
    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    } elseif (mb_strlen($nama) < 2 || mb_strlen($nama) > 50) {
        $errors[] = 'Nama harus 2-50 karakter.';
    }

    // Validasi email
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (find_user_by_email($email)) {
        $errors[] = 'Email sudah terdaftar. Gunakan email lain atau login.';
    }

    // Validasi password
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (!$errors) {
        $users   = load_users();
        $users[] = [
            'id'               => bin2hex(random_bytes(8)),
            'nama'             => $nama,
            'email'            => strtolower($email),
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'created_at'       => date('Y-m-d H:i:s'),
            'remember_token'   => null,
            'remember_expires' => null,
        ];

        if (save_users($users)) {
            set_flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        }
        $errors[] = 'Gagal menyimpan data. Periksa izin folder data/.';
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Daftar Akun</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password <small>(min. 8 karakter)</small></label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Daftar</button>
    </form>

    <p class="muted">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
