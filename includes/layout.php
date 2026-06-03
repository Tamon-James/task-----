<?php
declare(strict_types=1);

function renderHeader(string $title): void
{
    $navItems = [
        'index.php' => 'ホーム',
        'tasks.php' => 'タスク',
        'daily_report.php' => '日報',
        'projects.php' => 'プロジェクト',
        'long_goals.php' => '目標',
        'schedule.php' => '予定',
    ];
    $current = basename($_SERVER['PHP_SELF']);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | Personal Task Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="index.php">Task & Report</a>
        <nav class="nav">
            <?php foreach ($navItems as $href => $label): ?>
                <a class="<?= $current === $href ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
            <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                <a href="logout.php">ログアウト</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <div class="page-title">
        <h1><?= e($title) ?></h1>
    </div>
<?php
}

function renderFooter(): void
{
    ?>
</main>
<script src="js/app.js"></script>
</body>
</html>
<?php
}
