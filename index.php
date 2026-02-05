<?php
/**
 * Точка входа в корень бота. Перенаправление в админку (обязательно /admin/).
 */
$adminUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? '')
    . rtrim(dirname($_SERVER['REQUEST_URI'] ?? ''), '/')
    . '/admin/';
header('Location: ' . $adminUrl, true, 302);
exit;
