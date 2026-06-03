<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/db/db_connect.php';
require_once __DIR__ . '/includes/layout.php';

$reportDate = $_POST['report_date'] ?? $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $params = [
        $_POST['report_date'],
        trim($_POST['done_text']),
        trim($_POST['not_done_text']),
        trim($_POST['tomorrow_task_text']),
        trim($_POST['available_time']),
        trim($_POST['fixed_schedule']),
        trim($_POST['awareness_text']),
        trim($_POST['risk_text']),
        trim($_POST['chatgpt_request']),
    ];

    $stmt = $pdo->prepare(
        "INSERT INTO daily_reports
            (report_date, done_text, not_done_text, tomorrow_task_text, available_time, fixed_schedule, awareness_text, risk_text, chatgpt_request)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            done_text = VALUES(done_text),
            not_done_text = VALUES(not_done_text),
            tomorrow_task_text = VALUES(tomorrow_task_text),
            available_time = VALUES(available_time),
            fixed_schedule = VALUES(fixed_schedule),
            awareness_text = VALUES(awareness_text),
            risk_text = VALUES(risk_text),
            chatgpt_request = VALUES(chatgpt_request)"
    );
    $stmt->execute($params);
    redirect('daily_report.php?date=' . urlencode($_POST['report_date']) . '&saved=1');
}

$stmt = $pdo->prepare('SELECT * FROM daily_reports WHERE report_date = ?');
$stmt->execute([$reportDate]);
$report = $stmt->fetch() ?: [
    'report_date' => $reportDate,
    'done_text' => '',
    'not_done_text' => '',
    'tomorrow_task_text' => '',
    'available_time' => '',
    'fixed_schedule' => '',
    'awareness_text' => '',
    'risk_text' => '',
    'chatgpt_request' => '',
];

$generatedReport = formatDateJa($report['report_date']) . "日報\n\n"
    . "【実施内容】\n" . ($report['done_text'] ?: '・') . "\n\n"
    . "【できなかったこと】\n" . ($report['not_done_text'] ?: '・') . "\n\n"
    . "【明日の課題】\n" . ($report['tomorrow_task_text'] ?: '・') . "\n\n"
    . "【明日使える時間】\n" . ($report['available_time'] ?: '') . "\n\n"
    . "【明日すでに入っている予定】\n" . ($report['fixed_schedule'] ?: '') . "\n\n"
    . "【気づき】\n" . ($report['awareness_text'] ?: '') . "\n\n"
    . "【リスク】\n" . ($report['risk_text'] ?: '') . "\n\n"
    . "【ChatGPTに相談したい内容】\n" . ($report['chatgpt_request'] ?: '');

renderHeader('日報管理');
?>
<?php if (isset($_GET['saved'])): ?>
    <div class="card" style="margin-bottom:18px;">日報を保存しました。</div>
<?php endif; ?>

<section class="grid">
    <div class="card">
        <div class="card-header">
            <h2>日報入力</h2>
        </div>
        <form method="post" class="form-grid">
            <?= csrfField() ?>
            <label class="form-full">日付
                <input type="date" name="report_date" value="<?= e($report['report_date']) ?>" required>
            </label>
            <label class="form-full">実施内容
                <textarea class="textarea-large" name="done_text"><?= e($report['done_text']) ?></textarea>
            </label>
            <label class="form-full">できなかったこと
                <textarea name="not_done_text"><?= e($report['not_done_text']) ?></textarea>
            </label>
            <label class="form-full">明日の課題
                <textarea name="tomorrow_task_text"><?= e($report['tomorrow_task_text']) ?></textarea>
            </label>
            <label>明日使える時間
                <input type="text" name="available_time" value="<?= e($report['available_time']) ?>" placeholder="例: 6時間">
            </label>
            <label>明日すでに入っている予定
                <input type="text" name="fixed_schedule" value="<?= e($report['fixed_schedule']) ?>" placeholder="例: ゼミ">
            </label>
            <label class="form-full">気づき
                <textarea name="awareness_text"><?= e($report['awareness_text']) ?></textarea>
            </label>
            <label class="form-full">リスク
                <textarea name="risk_text"><?= e($report['risk_text']) ?></textarea>
            </label>
            <label class="form-full">ChatGPTに相談したい内容
                <textarea name="chatgpt_request"><?= e($report['chatgpt_request']) ?></textarea>
            </label>
            <div class="actions form-full">
                <button type="submit">保存</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>ChatGPT用日報</h2>
            <button type="button" data-copy-target="#generated-report">コピー</button>
        </div>
        <textarea id="generated-report" class="copy-box" readonly><?= e($generatedReport) ?></textarea>
    </div>
</section>
<?php renderFooter(); ?>
