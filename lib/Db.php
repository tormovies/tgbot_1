<?php
/**
 * SQLite: логи запросов/ответов и состояние "ожидаю текст сна".
 */
class Db
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dir = defined('DATA_DIR') ? DATA_DIR : (__DIR__ . '/../data');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $path = rtrim($dir, '/\\') . '/bot.sqlite';
            self::$pdo = new PDO('sqlite:' . $path);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::migrate();
        }
        return self::$pdo;
    }

    private static function migrate(): void
    {
        $pdo = self::$pdo;
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                username TEXT,
                chat_id INTEGER NOT NULL,
                chat_type TEXT,
                dream_text TEXT NOT NULL,
                interpretation TEXT NOT NULL,
                created_at TEXT NOT NULL
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS state (
                user_id INTEGER NOT NULL,
                chat_id INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                PRIMARY KEY (user_id, chat_id)
            )
        ");
    }

    /** Ждём текст сна от пользователя в этом чате */
    public static function setWaiting(int $userId, int $chatId): void
    {
        $pdo = self::get();
        $pdo->prepare("REPLACE INTO state (user_id, chat_id, created_at) VALUES (?, ?, datetime('now'))")
            ->execute([$userId, $chatId]);
    }

    public static function isWaiting(int $userId, int $chatId): bool
    {
        $pdo = self::get();
        $st = $pdo->prepare("SELECT 1 FROM state WHERE user_id = ? AND chat_id = ?");
        $st->execute([$userId, $chatId]);
        return (bool) $st->fetchColumn();
    }

    public static function clearWaiting(int $userId, int $chatId): void
    {
        self::get()->prepare("DELETE FROM state WHERE user_id = ? AND chat_id = ?")
            ->execute([$userId, $chatId]);
    }

    private const LOGS_TABLE_SCHEMA = "
        CREATE TABLE %s (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            username TEXT,
            chat_id INTEGER NOT NULL,
            chat_type TEXT,
            dream_text TEXT NOT NULL,
            interpretation TEXT NOT NULL,
            created_at TEXT NOT NULL
        )
    ";

    public static function addLog(int $userId, ?string $username, int $chatId, string $chatType, string $dreamText, string $interpretation): void
    {
        $pdo = self::get();
        $pdo->prepare("
            INSERT INTO logs (user_id, username, chat_id, chat_type, dream_text, interpretation, created_at)
            VALUES (?, ?, ?, ?, ?, ?, datetime('now'))
        ")->execute([$userId, $username, $chatId, $chatType, $dreamText, $interpretation]);

        $limit = defined('LOGS_ARCHIVE_AFTER') ? (int) LOGS_ARCHIVE_AFTER : 0;
        if ($limit > 0) {
            $n = (int) $pdo->query("SELECT COUNT(*) FROM logs")->fetchColumn();
            if ($n >= $limit) {
                self::rotateLogsTable($pdo);
            }
        }
    }

    /** Переименовать logs в архивную таблицу и создать новую пустую logs. */
    private static function rotateLogsTable(PDO $pdo): void
    {
        $archiveName = 'logs_archive_' . date('Ymd_His');
        $pdo->exec("ALTER TABLE logs RENAME TO " . self::safeTableName($archiveName));
        $pdo->exec(sprintf(self::LOGS_TABLE_SCHEMA, 'logs'));
    }

    private static function safeTableName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $name) ?: 'logs_archive';
    }

    /** Имена таблиц логов: logs (текущая) + logs_archive_* (по убыванию даты в имени). */
    public static function getLogTableNames(): array
    {
        $pdo = self::get();
        $st = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND (name='logs' OR name LIKE 'logs_archive_%') ORDER BY name DESC");
        $out = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $out[] = $row['name'];
        }
        return $out;
    }

    /** Список логов из указанной таблицы, новые сверху. */
    public static function getLogs(int $limit = 200, string $table = 'logs'): array
    {
        $table = self::safeTableName($table);
        $pdo = self::get();
        $st = $pdo->prepare("
            SELECT id, user_id, username, chat_id, chat_type, dream_text, interpretation, created_at
            FROM " . $table . " ORDER BY id DESC LIMIT ?
        ");
        $st->execute([$limit]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
