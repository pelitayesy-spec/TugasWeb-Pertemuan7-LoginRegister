<?php
require __DIR__ . '/includes/functions.php';
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
