# Деплой обновлений

**Каталог бота** — та папка на сервере, где лежит `bot.php` (и `git pull`, если репозиторий клонирован туда). Задай один раз переменную в SSH-сессии:

```bash
export BOT_DIR=/полный/путь/к/папке/бота
# пример: export BOT_DIR=/var/www/html/tgbot98
```

Дальше все команды используют `$BOT_DIR`.

---

## 1. Закоммитить и запушить (на своём ПК)

В PowerShell перейди в **свою** локальную копию репозитория (путь у каждого свой):

```powershell
cd "C:\путь\к\папке\проекта"
git add .
git status
git commit -m "описание изменений"
git push origin main
```

---

## 2. Обновить код на сервере (по SSH)

```bash
cd "$BOT_DIR"
git pull origin main
```

Если папки с ботом ещё нет в git, скопируй файлы вручную (FTP/панель) или клонируй в **свой** каталог:

```bash
cd /родительский/каталог/сайта
git clone https://github.com/tormovies/tgbot_1.git имя_папки_бота
cd имя_папки_бота
cp config.sample.php config.php
nano config.php   # заполнить BOT_TOKEN, DEEPSEEK_API_KEY, ADMIN_*
```

---

## 3. Прокси к Telegram (если нужен)

В **`$BOT_DIR`** создай файл **`.proxy.env`** (в git не входит), например:

```bash
nano "$BOT_DIR/.proxy.env"
```

Пример строки:

```bash
export HTTPS_PROXY=http://IP_ПРОКСИ:8888
```

Скопировать шаблон: `cp .proxy.env.example .proxy.env` и раскомментировать/править.

---

## 4. Перезапустить бота (новый код и config)

```bash
pkill -f "php bot.php"
cd "$BOT_DIR"
set -a
[ -f .proxy.env ] && . ./.proxy.env
set +a
nohup php bot.php >> data/bot.log 2>&1 &
```

Проверка:

```bash
pgrep -af bot.php
tail -5 "$BOT_DIR/data/bot.log"
```

Если **cron** запускает `bot-watchdog.sh` из каталога бота, в начале скрипта подхватывается `$BOT_DIR/.proxy.env` (см. `bot-watchdog.sh`).

---

## Кратко: только обновление после git pull

```bash
export BOT_DIR=/полный/путь/к/папке/бота
cd "$BOT_DIR"
git pull origin main
pkill -f "php bot.php"
set -a
[ -f .proxy.env ] && . ./.proxy.env
set +a
nohup php bot.php >> data/bot.log 2>&1 &
```
