<?php
require __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $user = find_user_by_email($email);

        // Pesan sengaja dibuat umum agar tidak membocorkan email yang terdaftar
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            login_user($user);

            // Upgrade hash otomatis jika algoritma default berubah
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                update_user($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
            }

            if ($remember) {
                set_remember_cookie($user['id']);
            }

            set_flash('success', 'Login berhasil. Selamat datang, ' . $user['nama'] . '!');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Login</h1>

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
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label class="checkbox">
            <input type="checkbox" name="remember"> Ingat saya (30 hari)
        </label>

        <button type="submit">Login</button>
    </form>

    <p class="muted">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
