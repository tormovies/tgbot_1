# Деплой обновлений

## 1. Закоммитить и запушить (на своём ПК)

```powershell
cd "c:\projects\telegramm bot"
git add .
git status
git commit -m "описание изменений"
git push origin main
```

---

## 2. Обновить код на сервере (по SSH)

```bash
cd ~/bot.snovidec.ru/public_html/tgbot98
git pull origin main
```

Если папка не из git (заливали вручную), тогда просто скопируй обновлённые файлы через FTP/панель или заново:

```bash
cd ~/bot.snovidec.ru/public_html
rm -rf tgbot98
git clone https://github.com/tormovies/tgbot_1.git tgbot98
cd tgbot98
cp config.sample.php config.php
nano config.php   # заполнить BOT_TOKEN, DEEPSEEK_API_KEY, ADMIN_*
```

---

## 3. Перезапустить бота (чтобы подхватил новый код и config)

```bash
pkill -f "php bot.php"
cd ~/bot.snovidec.ru/public_html/tgbot98
nohup php bot.php >> data/bot.log 2>&1 &
```

Проверить, что процесс запущен:

```bash
pgrep -af bot.php
tail -5 data/bot.log
```

---

## Кратко: только обновление после git pull

```bash
cd ~/bot.snovidec.ru/public_html/tgbot98
git pull origin main
pkill -f "php bot.php"
nohup php bot.php >> data/bot.log 2>&1 &
```
