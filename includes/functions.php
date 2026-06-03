<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function selected(?string $current, string $value): string
{
    return $current === $value ? 'selected' : '';
}

function checkedStatus(?string $current, string $value): string
{
    return $current === $value ? 'checked' : '';
}

function fetchProjects(PDO $pdo): array
{
    return $pdo->query('SELECT id, name FROM projects ORDER BY name')->fetchAll();
}

function formatMinutes($minutes): string
{
    if ($minutes === null || $minutes === '') {
        return '-';
    }

    return (string) $minutes . '分';
}

function formatDateJa(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('Y年n月j日', strtotime($date));
}
