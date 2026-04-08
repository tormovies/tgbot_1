#!/bin/bash
# Сторож: если бот не запущен — запускает его.
# Запускать по cron каждые 5 минут: */5 * * * * /path/to/bot-watchdog.sh

# Каталог с bot.php: по умолчанию — папка, где лежит этот скрипт (положи watchdog в корень бота).
# Если скрипт вызывается из другого места — раскомментируй и задай путь вручную:
# BOT_DIR="/полный/путь/к/боту"
BOT_DIR="$(cd "$(dirname "$0")" && pwd)"

if [[ -f "$BOT_DIR/.proxy.env" ]]; then
    set -a
    # shellcheck source=/dev/null
    . "$BOT_DIR/.proxy.env"
    set +a
fi

if ! pgrep -f "php bot.php" > /dev/null; then
    cd "$BOT_DIR" || exit 1
    nohup php bot.php >> data/bot.log 2>&1 &
    echo "$(date '+%Y-%m-%d %H:%M:%S') [watchdog] Bot was down, started." >> "$BOT_DIR/data/bot.log"
fi
