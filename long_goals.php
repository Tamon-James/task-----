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
        $stmt = $pdo->prepare('DELETE FROM long_goals WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        redirect('long_goals.php');
    }

    $params = [
        trim($_POST['title']),
        trim($_POST['description']),
        $_POST['deadline'] !== '' ? $_POST['deadline'] : null,
        trim($_POST['current_status']),
        trim($_POST['issue_text']),
        trim($_POST['next_action']),
    ];

    if ($action === 'update') {
        $params[] = (int) $_POST['id'];
        $stmt = $pdo->prepare('UPDATE long_goals SET title = ?, description = ?, deadline = ?, current_status = ?, issue_text = ?, next_action = ? WHERE id = ?');
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare('INSERT INTO long_goals (title, description, deadline, current_status, issue_text, next_action) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute($params);
    }

    redirect('long_goals.php');
}

$editGoal = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM long_goals WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editGoal = $stmt->fetch() ?: null;
}

$goals = $pdo->query('SELECT * FROM long_goals ORDER BY COALESCE(deadline, "9999-12-31"), id DESC')->fetchAll();

renderHeader('中長期目標管理');
?>
<section class="card">
    <div class="card-header">
        <h2><?= $editGoal ? '目標編集' : '目標作成' ?></h2>
    </div>
    <form method="post" class="form-grid">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editGoal ? 'update' : 'create' ?>">
        <?php if ($editGoal): ?>
            <input type="hidden" name="id" value="<?= e((string) $editGoal['id']) ?>">
        <?php endif; ?>

        <label class="form-full">目標名
            <input type="text" name="title" required value="<?= e($editGoal['title'] ?? '') ?>">
        </label>
        <label>期限
            <input type="date" name="deadline" value="<?= e($editGoal['deadline'] ?? '') ?>">
        </label>
        <label class="form-full">目標詳細
            <textarea name="description"><?= e($editGoal['description'] ?? '') ?></textarea>
        </label>
        <label class="form-full">現状
            <textarea name="current_status"><?= e($editGoal['current_status'] ?? '') ?></textarea>
        </label>
        <label class="form-full">課題
            <textarea name="issue_text"><?= e($editGoal['issue_text'] ?? '') ?></textarea>
        </label>
        <label class="form-full">次のアクション
            <textarea name="next_action"><?= e($editGoal['next_action'] ?? '') ?></textarea>
        </label>
        <div class="actions form-full">
            <button type="submit"><?= $editGoal ? '更新' : '作成' ?></button>
            <?php if ($editGoal): ?>
                <a class="button secondary" href="long_goals.php">キャンセル</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card" style="margin-top:18px;">
    <div class="card-header">
        <h2>目標一覧</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>目標</th>
                    <th>期限</th>
                    <th>現状</th>
                    <th>次のアクション</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($goals as $goal): ?>
                    <tr>
                        <td data-label="目標">
                            <strong><?= e($goal['title']) ?></strong><br>
                            <span class="muted"><?= nl2br(e($goal['description'])) ?></span>
                        </td>
                        <td data-label="期限"><?= e(formatDateJa($goal['deadline'])) ?></td>
                        <td data-label="現状"><?= nl2br(e($goal['current_status'])) ?><br><span class="muted">課題: <?= nl2br(e($goal['issue_text'])) ?></span></td>
                        <td data-label="次のアクション"><?= nl2br(e($goal['next_action'])) ?></td>
                        <td data-label="操作">
                            <a class="button secondary" href="long_goals.php?edit=<?= e((string) $goal['id']) ?>">編集</a>
                            <form class="inline-form" method="post" onsubmit="return confirm('削除しますか？');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string) $goal['id']) ?>">
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
