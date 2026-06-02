<?php
declare(strict_types=1);

require_once __DIR__ . '/db/db_connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        redirect('tasks.php');
    }

    $projectId = $_POST['project_id'] !== '' ? (int) $_POST['project_id'] : null;
    $estimatedMinutes = $_POST['estimated_minutes'] !== '' ? (int) $_POST['estimated_minutes'] : null;
    $params = [
        $projectId,
        trim($_POST['title']),
        trim($_POST['description']),
        $_POST['priority'],
        $_POST['task_date'] !== '' ? $_POST['task_date'] : null,
        $estimatedMinutes,
        $_POST['status'],
    ];

    if ($action === 'update') {
        $params[] = (int) $_POST['id'];
        $stmt = $pdo->prepare(
            'UPDATE tasks SET project_id = ?, title = ?, description = ?, priority = ?, task_date = ?, estimated_minutes = ?, status = ? WHERE id = ?'
        );
        $stmt->execute($params);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO tasks (project_id, title, description, priority, task_date, estimated_minutes, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute($params);
    }

    redirect('tasks.php');
}

$editTask = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editTask = $stmt->fetch() ?: null;
}

$projects = fetchProjects($pdo);
$query = trim($_GET['q'] ?? '');
if ($query !== '') {
    $stmt = $pdo->prepare(
        "SELECT t.*, p.name AS project_name
         FROM tasks t
         LEFT JOIN projects p ON p.id = t.project_id
         WHERE t.title LIKE ? OR t.description LIKE ? OR p.name LIKE ?
         ORDER BY COALESCE(t.task_date, '9999-12-31'), t.id DESC"
    );
    $keyword = '%' . $query . '%';
    $stmt->execute([$keyword, $keyword, $keyword]);
    $tasks = $stmt->fetchAll();
} else {
    $tasks = $pdo->query(
        "SELECT t.*, p.name AS project_name
         FROM tasks t
         LEFT JOIN projects p ON p.id = t.project_id
         ORDER BY COALESCE(t.task_date, '9999-12-31'), t.id DESC"
    )->fetchAll();
}

renderHeader('タスク管理');
?>
<section class="card">
    <div class="card-header">
        <h2><?= $editTask ? 'タスク編集' : 'タスク作成' ?></h2>
    </div>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="<?= $editTask ? 'update' : 'create' ?>">
        <?php if ($editTask): ?>
            <input type="hidden" name="id" value="<?= e((string) $editTask['id']) ?>">
        <?php endif; ?>

        <label class="form-full">タスク名
            <input type="text" name="title" required value="<?= e($editTask['title'] ?? '') ?>">
        </label>

        <label>所属プロジェクト
            <select name="project_id">
                <option value="">なし</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= e((string) $project['id']) ?>" <?= selected(isset($editTask['project_id']) ? (string) $editTask['project_id'] : null, (string) $project['id']) ?>>
                        <?= e($project['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>優先度
            <select name="priority">
                <?php foreach (['高', '中', '低'] as $priority): ?>
                    <option value="<?= e($priority) ?>" <?= selected($editTask['priority'] ?? '中', $priority) ?>><?= e($priority) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>予定日
            <input type="date" name="task_date" value="<?= e($editTask['task_date'] ?? date('Y-m-d')) ?>">
        </label>

        <label>予定作業時間 分
            <input type="number" name="estimated_minutes" min="0" value="<?= e(isset($editTask['estimated_minutes']) ? (string) $editTask['estimated_minutes'] : '') ?>">
        </label>

        <label>ステータス
            <select name="status">
                <?php foreach (['未着手', '進行中', '完了', '保留'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($editTask['status'] ?? '未着手', $status) ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="form-full">詳細
            <textarea name="description"><?= e($editTask['description'] ?? '') ?></textarea>
        </label>

        <div class="actions form-full">
            <button type="submit"><?= $editTask ? '更新' : '作成' ?></button>
            <?php if ($editTask): ?>
                <a class="button secondary" href="tasks.php">キャンセル</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card" style="margin-top:18px;">
    <div class="card-header">
        <h2>タスク一覧</h2>
    </div>
    <form class="search-row" method="get">
        <input type="search" name="q" placeholder="タスク名、詳細、プロジェクトで検索" value="<?= e($query) ?>">
        <button type="submit">検索</button>
        <a class="button secondary" href="tasks.php">リセット</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>タスク</th>
                    <th>プロジェクト</th>
                    <th>優先度</th>
                    <th>予定日</th>
                    <th>時間</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td data-label="タスク">
                            <strong><?= e($task['title']) ?></strong><br>
                            <span class="muted"><?= nl2br(e($task['description'])) ?></span>
                        </td>
                        <td data-label="プロジェクト"><?= e($task['project_name'] ?? '-') ?></td>
                        <td data-label="優先度"><span class="badge <?= $task['priority'] === '高' ? 'high' : ($task['priority'] === '中' ? 'medium' : 'low') ?>"><?= e($task['priority']) ?></span></td>
                        <td data-label="予定日"><?= e(formatDateJa($task['task_date'])) ?></td>
                        <td data-label="時間"><?= e(formatMinutes($task['estimated_minutes'])) ?></td>
                        <td data-label="状態"><?= e($task['status']) ?></td>
                        <td data-label="操作">
                            <a class="button secondary" href="tasks.php?edit=<?= e((string) $task['id']) ?>">編集</a>
                            <form class="inline-form" method="post" onsubmit="return confirm('削除しますか？');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e((string) $task['id']) ?>">
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

