<?php
declare(strict_types=1);

$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    exit('config.php がありません。config.example.php をコピーして設定してください。');
}

require_once $configPath;

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    exit('データベースに接続できません。sql/create_tables.sql をインポートしてから再度お試しください。');
}
