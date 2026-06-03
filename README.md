# Personal Task & Daily Report Manager

個人向けのタスク管理、目標管理、スケジュール管理、日報作成アプリです。

## 初期設定

1. `config.example.php` を `config.php` にコピーします。
2. `config.php` のDB接続情報を本番環境に合わせて変更します。
3. ログイン用パスワードハッシュを作成します。

```powershell
C:\xampp\php\php.exe -r "echo password_hash('ここに強いパスワード', PASSWORD_DEFAULT), PHP_EOL;"
```

4. 出力された値を `config.php` の `APP_PASSWORD_HASH` に設定します。
5. `sql/create_tables.sql` をMySQLへインポートします。

## 公開時の注意

- `config.php` はGit管理しません。
- 公開サーバーへアップロードしたDBパスワードは、漏えいした可能性がある場合は必ず変更してください。
- 公開環境ではHTTPSで利用してください。

