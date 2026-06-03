<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$next = $_GET['next'] ?? 'index.php';
$allowedNext = ['index.php', 'tasks.php', 'daily_report.php', 'projects.php', 'long_goals.php', 'schedule.php'];
if (!in_array($next, $allowedNext, true)) {
    $next = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $user = trim($_POST['user'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($user === APP_USER && password_verify($password, APP_PASSWORD_HASH)) {
        loginUser();
        redirect($next);
    }

    $error = 'ユーザー名またはパスワードが違います。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | Personal Task Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="login-page">
    <section class="card login-card">
        <h1>ログイン</h1>
        <p class="muted">Personal Task & Daily Report Manager</p>
        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" class="form-grid">
            <?= csrfField() ?>
            <label class="form-full">ユーザー名
                <input type="text" name="user" autocomplete="username" required>
            </label>
            <label class="form-full">パスワード
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <div class="actions form-full">
                <button type="submit">ログイン</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>

