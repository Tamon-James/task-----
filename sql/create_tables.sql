SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS personal_task_manager
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE personal_task_manager;

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('進行中', '完了', '保留') NOT NULL DEFAULT '進行中',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    priority ENUM('高', '中', '低') NOT NULL DEFAULT '中',
    task_date DATE NULL,
    estimated_minutes INT NULL,
    status ENUM('未着手', '進行中', '完了', '保留') NOT NULL DEFAULT '未着手',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS daily_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_date DATE NOT NULL UNIQUE,
    done_text TEXT NULL,
    not_done_text TEXT NULL,
    tomorrow_task_text TEXT NULL,
    available_time VARCHAR(100) NULL,
    fixed_schedule TEXT NULL,
    awareness_text TEXT NULL,
    risk_text TEXT NULL,
    chatgpt_request TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS long_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    deadline DATE NULL,
    current_status TEXT NULL,
    issue_text TEXT NULL,
    next_action TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    title VARCHAR(255) NOT NULL,
    memo TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO projects (name, description, status)
SELECT 'タスク管理アプリ開発', 'Personal Task & Daily Report Manager の開発', '進行中'
WHERE NOT EXISTS (SELECT 1 FROM projects WHERE name = 'タスク管理アプリ開発');

INSERT INTO projects (name, description, status)
SELECT '卒業研究', '研究進捗管理用プロジェクト', '進行中'
WHERE NOT EXISTS (SELECT 1 FROM projects WHERE name = '卒業研究');
