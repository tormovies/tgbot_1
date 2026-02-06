#!/bin/bash
# Сторож: если бот не запущен — запускает его.
# Запускать по cron каждые 5 минут: */5 * * * * /path/to/bot-watchdog.sh

BOT_DIR="/home/admin/domains/website.com.ru/public_html/tgbot98"
# Или подставь свой путь: BOT_DIR="$(dirname "$0")"

if ! pgrep -f "php bot.php" > /dev/null; then
    cd "$BOT_DIR" || exit 1
    nohup php bot.php >> data/bot.log 2>&1 &
    echo "$(date '+%Y-%m-%d %H:%M:%S') [watchdog] Bot was down, started." >> "$BOT_DIR/data/bot.log"
fi
