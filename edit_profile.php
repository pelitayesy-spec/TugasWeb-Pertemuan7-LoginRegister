<?php
require __DIR__ . '/includes/functions.php';
require_login();

$user   = current_user();
$errors = [];
$nama   = $user['nama'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nama        = trim($_POST['nama'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $changes     = [];

    if ($nama === '' || mb_strlen($nama) < 2 || mb_strlen($nama) > 50) {
        $errors[] = 'Nama harus 2-50 karakter.';
    } else {
        $changes['nama'] = $nama;
    }

    // Ganti password bersifat opsional
    if ($newPass !== '') {
        if (!password_verify($currentPass, $user['password'])) {
            $errors[] = 'Password saat ini salah.';
        } elseif (strlen($newPass) < 8) {
            $errors[] = 'Password baru minimal 8 karakter.';
        } else {
            $changes['password'] = password_hash($newPass, PASSWORD_DEFAULT);
        }
    }

    if (!$errors && update_user($user['id'], $changes)) {
        set_flash('success', 'Profil berhasil diperbarui.');
        redirect('dashboard.php');
    }
}

$pageTitle = 'Edit Profil';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Edit Profil</h1>

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

        <label>Email</label>
        <input type="email" value="<?= e($user['email']) ?>" disabled>

        <hr>
        <p class="muted">Kosongkan jika tidak ingin mengganti password.</p>

        <label for="current_password">Password Saat Ini</label>
        <input type="password" id="current_password" name="current_password">

        <label for="new_password">Password Baru</label>
        <input type="password" id="new_password" name="new_password">

        <button type="submit">Simpan</button>
    </form>

    <p class="muted"><a href="dashboard.php">&larr; Kembali ke dashboard</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
