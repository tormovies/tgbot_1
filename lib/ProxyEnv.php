<?php
/**
 * Загрузка переменных из .proxy.env (export VAR=value или VAR=value) через putenv.
 * Пустые строки и строки с # в начале (после trim) пропускаются. Файл может отсутствовать.
 * Совместимость: PHP 5.6+
 */

/**
 * @param string $path полный путь к файлу
 */
function load_proxy_env_file($path)
{
    if (!is_string($path) || $path === '' || !is_file($path) || !is_readable($path)) {
        return;
    }
    $lines = @file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        if (strncmp($line, 'export ', 7) === 0) {
            $line = trim(substr($line, 7));
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $name = trim(substr($line, 0, $eq));
        $value = trim(substr($line, $eq + 1));
        if ($name === '') {
            continue;
        }
        putenv($name . '=' . $value);
    }
}
