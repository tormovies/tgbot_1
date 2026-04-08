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

PHP_BIN="/usr/local/php83/bin/php"
[[ -x "$PHP_BIN" ]] || PHP_BIN="php"

bot_running=0
if pgrep -f "$BOT_DIR/bot.php" > /dev/null; then
    bot_running=1
else
    for pid in $(pgrep -f "php bot.php" 2>/dev/null); do
        [[ "$(readlink -f "/proc/$pid/cwd" 2>/dev/null)" == "$BOT_DIR" ]] && bot_running=1 && break
    done
fi

if [[ "$bot_running" -eq 0 ]]; then
    cd "$BOT_DIR" || exit 1
    nohup "$PHP_BIN" "$BOT_DIR/bot.php" >> data/bot.log 2>&1 &
    echo "$(date '+%Y-%m-%d %H:%M:%S') [watchdog] Bot was down, started." >> "$BOT_DIR/data/bot.log"
fi
