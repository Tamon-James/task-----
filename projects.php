<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/db/db_connect.php';
require_once __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        redirect('projects.php');
    }

    $params = [
        trim($_POST['name']),
        trim($_POST['description']),
        $_POST['start_date'] !== '' ? $_POST['start_date'] : null,
        $_POST['end_date'] !== '' ? $_POST['end_date'] : null,
        $_POST['status'],
    ];

    if ($action === 'update') {
        $params[] = (int) $_POST['id'];
        $stmt = $pdo->prepare('UPDATE projects SET name = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?');
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare('INSERT INTO projects (name, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute($params);
    }

    redirect('projects.php');
}

$editProject = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editProject = $stmt->fetch() ?: null;
}

$projects = $pdo->query('SELECT * FROM projects ORDER BY status, COALESCE(end_date, "9999-12-31"), id DESC')->fetchAll();

renderHeader('プロジェクト管理');
?>
<section class="card">
    <div class="card-header">
        <h2><?= $editProject ? 'プロジェクト編集' : 'プロジェクト作成' ?></h2>
    </div>
    <form method="post" class="form-grid">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editProject ? 'update' : 'create' ?>">
        <?php if ($editProject): ?>
            <input type="hidden" name="id" value="<?= e((string) $editProject['id']) ?>">
        <?php endif; ?>

        <label class="form-full">プロジェクト名
            <input type="text" name="name" required value="<?= e($editProject['name'] ?? '') ?>">
        </label>
        <label>開始日
            <input type="date" name="start_date" value="<?= e($editProject['start_date'] ?? '') ?>">
        </label>
        <label>終了予定日
            <input type="date" name="end_date" value="<?= e($editProject['end_date'] ?? '') ?>">
        </label>
        <label>状態
            <select name="status">
                <?php foreach (['進行中', '完了', '保留'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($editProject['status'] ?? '進行中', $status) ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="form-full">説明
            <textarea name="description"><?= e($editProject['description'] ?? '') ?></textarea>
        </label>
        <div class="actions form-full">
            <button type="submit"><?= $editProject ? '更新' : '作成' ?></button>
            <?php if ($editProject): ?>
                <a class="button secondary" href="projects.php">キャンセル</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card" style="margin-top:18px;">
    <div class="card-header">
        <h2>プロジェクト一覧</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>プロジェクト</th>
                    <th>期間</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td data-label="プロジェクト">
                            <strong><?= e($project['name']) ?></strong><br>
                            <span class="muted"><?= nl2br(e($project['description'])) ?></span>
                        </td>
                        <td data-label="期間"><?= e(formatDateJa($project['start_date'])) ?> - <?= e(formatDateJa($project['end_date'])) ?></td>
                        <td data-label="状態"><?= e($project['status']) ?></td>
                        <td data-label="操作">
                            <a class="button secondary" href="projects.php?edit=<?= e((string) $project['id']) ?>">編集</a>
                            <form class="inline-form" method="post" onsubmit="return confirm('削除しますか？');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string) $project['id']) ?>">
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
