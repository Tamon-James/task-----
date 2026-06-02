<?php
declare(strict_types=1);

require_once __DIR__ . '/db/db_connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/layout.php';

$today = date('Y-m-d');
$weekEnd = date('Y-m-d', strtotime('+7 days'));

$todayTasksStmt = $pdo->prepare(
    "SELECT t.*, p.name AS project_name
     FROM tasks t
     LEFT JOIN projects p ON p.id = t.project_id
     WHERE t.task_date = ?
     ORDER BY FIELD(t.priority, '高', '中', '低'), t.id DESC"
);
$todayTasksStmt->execute([$today]);
$todayTasks = $todayTasksStmt->fetchAll();

$incompleteTasks = $pdo->query(
    "SELECT t.*, p.name AS project_name
     FROM tasks t
     LEFT JOIN projects p ON p.id = t.project_id
     WHERE t.status <> '完了'
     ORDER BY COALESCE(t.task_date, '9999-12-31'), FIELD(t.priority, '高', '中', '低')
     LIMIT 8"
)->fetchAll();

$scheduleStmt = $pdo->prepare(
    'SELECT * FROM schedules WHERE schedule_date = ? ORDER BY start_time, id'
);
$scheduleStmt->execute([$today]);
$todaySchedules = $scheduleStmt->fetchAll();

$weeklyGoalsStmt = $pdo->prepare(
    'SELECT * FROM long_goals WHERE deadline IS NULL OR deadline <= ? ORDER BY COALESCE(deadline, "9999-12-31"), id DESC LIMIT 5'
);
$weeklyGoalsStmt->execute([$weekEnd]);
$weeklyGoals = $weeklyGoalsStmt->fetchAll();

$goals = $pdo->query('SELECT * FROM long_goals ORDER BY COALESCE(deadline, "9999-12-31"), id DESC LIMIT 5')->fetchAll();
$todayGoal = $goals[0]['next_action'] ?? '今日の目標を long_goals.php から登録してください。';
$taskCount = count($todayTasks);
$doneCount = count(array_filter($todayTasks, fn(array $task): bool => $task['status'] === '完了'));
$completionRate = $taskCount > 0 ? round(($doneCount / $taskCount) * 100) : 0;

renderHeader('ホームダッシュボード');
?>
<section class="grid">
    <div class="card">
        <div class="card-header">
            <h2>今日の日付</h2>
        </div>
        <p class="stat"><?= e(formatDateJa($today)) ?></p>
        <p class="muted">今日のタスク完了率: <?= e((string) $completionRate) ?>%</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>今日の目標</h2>
        </div>
        <p><?= nl2br(e($todayGoal)) ?></p>
        <a class="button secondary" href="long_goals.php">目標を確認</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>本日のタスク</h2>
            <a class="button secondary" href="tasks.php">追加</a>
        </div>
        <?php if (!$todayTasks): ?>
            <div class="empty">今日のタスクはまだありません。</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($todayTasks as $task): ?>
                    <li class="list-item">
                        <div class="item-title"><?= e($task['title']) ?></div>
                        <div class="item-meta">
                            <span class="badge <?= $task['priority'] === '高' ? 'high' : ($task['priority'] === '中' ? 'medium' : 'low') ?>"><?= e($task['priority']) ?></span>
                            <span><?= e($task['status']) ?></span>
                            <span><?= e($task['project_name'] ?? 'プロジェクトなし') ?></span>
                            <span><?= e(formatMinutes($task['estimated_minutes'])) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>本日のスケジュール</h2>
            <a class="button secondary" href="schedule.php">追加</a>
        </div>
        <?php if (!$todaySchedules): ?>
            <div class="empty">今日の予定はまだありません。</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($todaySchedules as $schedule): ?>
                    <li class="list-item">
                        <div class="item-title"><?= e($schedule['title']) ?></div>
                        <div class="item-meta">
                            <span><?= e(substr((string) $schedule['start_time'], 0, 5)) ?> - <?= e(substr((string) $schedule['end_time'], 0, 5)) ?></span>
                        </div>
                        <?php if ($schedule['memo']): ?>
                            <p><?= nl2br(e($schedule['memo'])) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>未完了タスク</h2>
            <a class="button secondary" href="tasks.php">一覧</a>
        </div>
        <?php if (!$incompleteTasks): ?>
            <div class="empty">未完了タスクはありません。</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($incompleteTasks as $task): ?>
                    <li class="list-item">
                        <div class="item-title"><?= e($task['title']) ?></div>
                        <div class="item-meta">
                            <span><?= e($task['status']) ?></span>
                            <span><?= e(formatDateJa($task['task_date'])) ?></span>
                            <span><?= e($task['project_name'] ?? 'プロジェクトなし') ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>今週の目標</h2>
        </div>
        <?php if (!$weeklyGoals): ?>
            <div class="empty">期限が近い目標はまだありません。</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($weeklyGoals as $goal): ?>
                    <li class="list-item">
                        <div class="item-title"><?= e($goal['title']) ?></div>
                        <div class="item-meta">
                            <span>期限: <?= e(formatDateJa($goal['deadline'])) ?></span>
                        </div>
                        <p><?= nl2br(e($goal['next_action'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>中長期目標の進捗</h2>
            <a class="button secondary" href="long_goals.php">管理</a>
        </div>
        <?php if (!$goals): ?>
            <div class="empty">中長期目標はまだありません。</div>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($goals as $goal): ?>
                    <li class="list-item">
                        <div class="item-title"><?= e($goal['title']) ?></div>
                        <p><?= nl2br(e($goal['current_status'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>日報</h2>
        </div>
        <p class="muted">夜に実施内容、明日の課題、ChatGPTへの相談内容をまとめます。</p>
        <a class="button" href="daily_report.php">日報を入力</a>
    </div>
</section>
<?php renderFooter(); ?>

