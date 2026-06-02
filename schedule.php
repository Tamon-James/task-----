<?php
declare(strict_types=1);

require_once __DIR__ . '/db/db_connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM schedules WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        redirect('schedule.php');
    }

    $params = [
        $_POST['schedule_date'],
        $_POST['start_time'] !== '' ? $_POST['start_time'] : null,
        $_POST['end_time'] !== '' ? $_POST['end_time'] : null,
        trim($_POST['title']),
        trim($_POST['memo']),
    ];

    if ($action === 'update') {
        $params[] = (int) $_POST['id'];
        $stmt = $pdo->prepare('UPDATE schedules SET schedule_date = ?, start_time = ?, end_time = ?, title = ?, memo = ? WHERE id = ?');
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare('INSERT INTO schedules (schedule_date, start_time, end_time, title, memo) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute($params);
    }

    redirect('schedule.php');
}

$editSchedule = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM schedules WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editSchedule = $stmt->fetch() ?: null;
}

$schedules = $pdo->query('SELECT * FROM schedules ORDER BY schedule_date DESC, start_time DESC, id DESC LIMIT 100')->fetchAll();

renderHeader('スケジュール管理');
?>
<section class="card">
    <div class="card-header">
        <h2><?= $editSchedule ? '予定編集' : '予定作成' ?></h2>
    </div>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="<?= $editSchedule ? 'update' : 'create' ?>">
        <?php if ($editSchedule): ?>
            <input type="hidden" name="id" value="<?= e((string) $editSchedule['id']) ?>">
        <?php endif; ?>

        <label>日付
            <input type="date" name="schedule_date" required value="<?= e($editSchedule['schedule_date'] ?? date('Y-m-d')) ?>">
        </label>
        <label>タイトル
            <input type="text" name="title" required value="<?= e($editSchedule['title'] ?? '') ?>">
        </label>
        <label>開始時間
            <input type="time" name="start_time" value="<?= e(isset($editSchedule['start_time']) ? substr((string) $editSchedule['start_time'], 0, 5) : '') ?>">
        </label>
        <label>終了時間
            <input type="time" name="end_time" value="<?= e(isset($editSchedule['end_time']) ? substr((string) $editSchedule['end_time'], 0, 5) : '') ?>">
        </label>
        <label class="form-full">メモ
            <textarea name="memo"><?= e($editSchedule['memo'] ?? '') ?></textarea>
        </label>
        <div class="actions form-full">
            <button type="submit"><?= $editSchedule ? '更新' : '作成' ?></button>
            <?php if ($editSchedule): ?>
                <a class="button secondary" href="schedule.php">キャンセル</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card" style="margin-top:18px;">
    <div class="card-header">
        <h2>予定一覧</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>時間</th>
                    <th>タイトル</th>
                    <th>メモ</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td data-label="日付"><?= e(formatDateJa($schedule['schedule_date'])) ?></td>
                        <td data-label="時間"><?= e(substr((string) $schedule['start_time'], 0, 5)) ?> - <?= e(substr((string) $schedule['end_time'], 0, 5)) ?></td>
                        <td data-label="タイトル"><strong><?= e($schedule['title']) ?></strong></td>
                        <td data-label="メモ"><?= nl2br(e($schedule['memo'])) ?></td>
                        <td data-label="操作">
                            <a class="button secondary" href="schedule.php?edit=<?= e((string) $schedule['id']) ?>">編集</a>
                            <form class="inline-form" method="post" onsubmit="return confirm('削除しますか？');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string) $schedule['id']) ?>">
                                <button class="danger" type="submit">削除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php renderFooter(); ?>

