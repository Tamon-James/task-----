<?php
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'jmestamon_task_table';
const DB_USER = 'jmestamon_task_table';
const DB_PASS = 'LDtbu3ePQ3V49b5LVEMh';

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

