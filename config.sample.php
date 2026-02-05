<?php
/**
 * Скопируйте этот файл в config.php и заполните значения.
 * config.php не должен попадать в репозиторий.
 */

define('BOT_TOKEN', '');           // Токен от @BotFather
define('DEEPSEEK_API_KEY', '');   // Ключ API DeepSeek

define('ADMIN_LOGIN', 'admin');   // Логин для входа в админку
define('ADMIN_PASSWORD', '');     // Пароль для входа в админку

// Путь к папке данных (оставьте пустым или __DIR__ . '/data')
define('DATA_DIR', __DIR__ . '/data');

// Ротация логов: при достижении этого числа записей в таблице logs
// она переименовывается в logs_archive_YYYYMMDD_HHMMSS и создаётся новая пустая.
// 0 = не переключать, писать в одну таблицу всегда.
define('LOGS_ARCHIVE_AFTER', 5000);
