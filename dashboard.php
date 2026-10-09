<?php
require __DIR__ . '/includes/functions.php';
require_login();

$user      = current_user();
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Dashboard</h1>
    <p>Halo, <strong><?= e($user['nama']) ?></strong>!</p>

    <table>
        <tr><th>Nama</th><td><?= e($user['nama']) ?></td></tr>
        <tr><th>Email</th><td><?= e($user['email']) ?></td></tr>
        <tr><th>Terdaftar</th><td><?= e($user['created_at']) ?></td></tr>
    </table>

    <div class="actions">
        <a class="btn btn-secondary" href="edit_profile.php">Edit Profil</a>
        <form method="post" action="logout.php">
            <?= csrf_field() ?>
            <button type="submit" class="btn-danger">Logout</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
