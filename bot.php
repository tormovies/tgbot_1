<?php
/**
 * Запуск: php bot.php
 * Держит long polling, обрабатывает /son и текст сна, ответ в личку.
 */
declare(strict_types=1);

$config = __DIR__ . '/config.php';
if (!is_file($config)) {
    echo "Создайте config.php из config.sample.php и заполните BOT_TOKEN и DEEPSEEK_API_KEY.\n";
    exit(1);
}
require $config;
require __DIR__ . '/lib/Db.php';
require __DIR__ . '/lib/Telegram.php';
require __DIR__ . '/lib/DeepSeek.php';

$tg = new Telegram(BOT_TOKEN);
$deepseek = new DeepSeek(DEEPSEEK_API_KEY);

$offset = null;
echo "Бот запущен. Ожидание сообщений...\n";

while (true) {
    try {
        $updates = $tg->getUpdates($offset, 30);
    } catch (Throwable $e) {
        echo date('Y-m-d H:i:s') . " getUpdates error: " . $e->getMessage() . "\n";
        sleep(5);
        continue;
    }

    foreach ($updates as $u) {
        $offset = ($u['update_id'] ?? 0) + 1;
        $msg = $u['message'] ?? null;
        if (!$msg) {
            continue;
        }

        $chatId = (int) $msg['chat']['id'];
        $chatType = $msg['chat']['type'] ?? 'private';
        $userId = (int) ($msg['from']['id'] ?? 0);
        $username = $msg['from']['username'] ?? null;
        $text = trim((string) ($msg['text'] ?? ''));

        try {
            if (Db::isWaiting($userId, $chatId)) {
                // Следующее сообщение после /son — текст сна
                if ($text === '') {
                    $tg->sendMessage($chatId, 'Пришли текст сна одним сообщением.');
                    continue;
                }
                Db::clearWaiting($userId, $chatId);
                $dreamText = $text;
                $interpretation = $deepseek->interpretDream($dreamText);
                Db::addLog($userId, $username, $chatId, $chatType, $dreamText, $interpretation);
                // Ответ только в личку
                $tg->sendMessage($userId, $interpretation);
                // В группе — короткое уведомление
                if ($chatId !== $userId) {
                    $tg->sendMessage($chatId, 'Расшифровка отправлена тебе в личные сообщения.');
                }
                continue;
            }

            if ($text === '/son' || $text === '/start') {
                if ($text === '/son') {
                    Db::setWaiting($userId, $chatId);
                    $tg->sendMessage($chatId, 'Опиши сон в следующем сообщении.');
                } else {
                    $tg->sendMessage($chatId, 'Привет. Чтобы расшифровать сон, отправь команду /son и затем опиши сон следующим сообщением.');
                }
            }
        } catch (Throwable $e) {
            echo date('Y-m-d H:i:s') . " Error: " . $e->getMessage() . "\n";
            $tg->sendMessage($chatId, 'Произошла ошибка, попробуй позже.');
        }
    }
}
