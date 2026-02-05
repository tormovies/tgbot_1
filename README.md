# Telegram-бот: расшифровка снов (DeepSeek)

Пользователь пишет `/son`, затем текст сна. Текст остаётся в чате, расшифровка приходит в личку. Все запросы и ответы сохраняются и видны в админке.

Репозиторий: [https://github.com/tormovies/tgbot_1](https://github.com/tormovies/tgbot_1)

---

## Коммит и пуш в GitHub

Если репозиторий ещё не привязан или нужно отправить код:

В **PowerShell** (не cmd):

```powershell
cd "c:\projects\telegramm bot"
# Если была ошибка "could not lock config" — удалите папку .git:
Remove-Item -Recurse -Force .git -ErrorAction SilentlyContinue
git init
git remote add origin https://github.com/tormovies/tgbot_1.git
git add .
git status
git commit -m "Dream bot: Telegram + DeepSeek, admin, SQLite logs rotation"
git branch -M main
git push -u origin main
```

(В cmd удаление папки: `rmdir /s /q .git`)

При первом пуше Git запросит логин/пароль GitHub (или токен вместо пароля). `config.php` и `bot.txt` в коммит не попадут (см. `.gitignore`).

---

## Требования

- PHP 8.1+ с расширениями: `curl`, `pdo_sqlite`, `json`
- Токен бота (BotFather) и API-ключ DeepSeek

## Установка (перенос папки)

1. Скопируйте папку проекта на сервер (или оставьте локально).
2. Создайте `config.php` из `config.sample.php` и заполните:
   - `BOT_TOKEN` — токен от @BotFather
   - `DEEPSEEK_API_KEY` — ключ с platform.deepseek.com
   - `ADMIN_LOGIN` и `ADMIN_PASSWORD` — для входа в админку
3. Папка `data/` должна быть доступна на запись (там создаётся `bot.sqlite` и хранятся логи).

## Запуск бота

В консоли на сервере (или у себя для теста):

```bash
php bot.php
```

Скрипт работает бесконечно (long polling). Для постоянной работы на сервере используйте systemd/supervisor или экран:

```bash
nohup php bot.php > bot.log 2>&1 &
```

или через systemd (пример юнита):

```ini
[Unit]
Description=Dream bot
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/telegramm bot
ExecStart=/usr/bin/php bot.php
Restart=always

[Install]
WantedBy=multi-user.target
```

## Админка

- URL: `https://ваш-сервер/путь-к-проекту/admin/`
- Вход: логин и пароль из `config.php`
- На странице «Логи» — все запросы (тексты снов) и ответы (расшифровки) в удобном виде.

Админку имеет смысл закрыть по паролю и на уровне веб-сервера (например, дополнительно Basic Auth или доступ только с локального IP).

## Привязка к группе

1. Добавьте бота в группу: «Добавить участников» → найти бота по @username.
2. В группе пользователи пишут `/son`, затем следующим сообщением — текст сна.
3. Расшифровка приходит в личные сообщения пользователю; в группе бот пишет: «Расшифровка отправлена тебе в личные сообщения».

Важно: пользователь должен хотя бы раз написать боту в личку (например, нажать «Старт» или отправить `/start`), иначе бот не сможет отправить ему сообщение в личку.

## Команды

- `/son` — начать ввод сна; следующим сообщением отправить текст сна.
- `/start` — приветствие и подсказка.

## Структура

- `config.php` — секреты (не коммитить в репозиторий)
- `config.sample.php` — образец конфига
- `bot.php` — скрипт бота (запуск из консоли)
- `lib/` — Telegram, DeepSeek, БД
- `data/` — SQLite и данные
- `admin/` — вход и просмотр логов

---

## Настройка с нуля (пошагово)

### 1. Клонирование на сервер или локально

```bash
git clone https://github.com/tormovies/tgbot_1.git
cd tgbot_1
```

### 2. Конфиг

```bash
cp config.sample.php config.php
# Отредактировать config.php: BOT_TOKEN, DEEPSEEK_API_KEY, ADMIN_LOGIN, ADMIN_PASSWORD
# При необходимости: DATA_DIR, LOGS_ARCHIVE_AFTER (0 = без ротации)
```

### 3. Права на папку данных

На Linux/сервере:

```bash
chmod 755 data
# или, если веб-сервер другой пользователь:
chown www-data:www-data data
```

### 4. Запуск бота

- **Проверка локально (Windows):** открыть терминал в папке проекта, выполнить `php bot.php`. Написать боту в Telegram `/son`, затем текст сна.
- **На сервере постоянно:** запускать через systemd (см. выше) или `nohup php bot.php > data/bot.log 2>&1 &`.

### 5. Админка

- Разместить проект в каталоге веб-сервера (Nginx/Apache), чтобы открывался URL вида `https://ваш-домен.ru/tgbot_1/admin/`.
- Зайти по логину/паролю из `config.php`, проверить логи.

### 6. Бот в группе

- В группе Telegram: «Добавить участников» → найти бота по @username.
- Участники пишут `/son`, затем текст сна; расшифровка приходит в личку. Каждый должен хотя бы раз написать боту в личку (например, /start).
