# Ошибка 403 Forbidden на bot.snovidec.ru/tgbot98/

**Путь на сервере:** `~/bot.snovidec.ru/public_html/tgbot98`

Пошагово проверь следующее.

---

## 1. Права на папки и файлы

На сервере (SSH) выполни из каталога, где лежит `tgbot98`:

```bash
cd ~/bot.snovidec.ru/public_html
# Папки — право на вход (execute) для всех
find tgbot98 -type d -exec chmod 755 {} \;
# Файлы — чтение для всех
find tgbot98 -type f -exec chmod 644 {} \;
chmod 755 tgbot98/data
# Владелец — пользователь веб-сервера (apache или www-data):
chown -R apache:apache tgbot98
# или: chown -R www-data:www-data tgbot98
```

Проверь, что **все родительские каталоги** до `tgbot98` тоже имеют право на вход (минимум `755`), иначе Apache не «зайдёт» в папку.

---

## 2. Корень сайта (DocumentRoot)

Убедись, что в виртуальном хосте для **bot.snovidec.ru** указан тот каталог, где лежит папка **tgbot98**.

Пример: если конфиг такой:

```apache
DocumentRoot "/var/www/bot.snovidec.ru/htdocs"
```

то по адресу `http://bot.snovidec.ru/tgbot98/` должна существовать папка:

`/var/www/bot.snovidec.ru/htdocs/tgbot98/`

Если у тебя корень сайта — другая папка, положи **tgbot98** именно туда и проверь путь в браузере.

---

## 3. Разрешение в Apache (AllowOverride)

В конфиге виртуального хоста для bot.snovidec.ru для этого каталога должно быть разрешено чтение `.htaccess`:

```apache
<Directory "/var/www/bot.snovidec.ru/htdocs">
    AllowOverride All
    Require all granted
</Directory>
```

После правок перезапусти Apache:

```bash
sudo systemctl reload apache2
# или
sudo service httpd reload
```

---

## 4. SELinux (если включён, часто CentOS/RHEL)

Если команды выше не помогли и SELinux включён:

```bash
# Подставь свой путь к каталогу
chcon -R -t httpd_sys_content_t /var/www/bot.snovidec.ru/htdocs/tgbot98
# Разрешить запись в data
chcon -R -t httpd_sys_rw_content_t /var/www/bot.snovidec.ru/htdocs/tgbot98/data
```

---

## 5. Что проверить в браузере

- Открывай именно: **http://bot.snovidec.ru/tgbot98/** (со слэшем в конце).
- Должна открыться админка или редирект на **http://bot.snovidec.ru/tgbot98/admin/**.
- Если 403 только на **/tgbot98/**, но **/tgbot98/admin/** открывается — значит, не хватает `DirectoryIndex`; в проекте уже есть **index.php** и **.htaccess** с `DirectoryIndex index.php`.

Если после всех шагов 403 остаётся — пришли вывод команд:

```bash
ls -la /путь/к/корню/сайта/
ls -la /путь/к/корню/сайта/tgbot98/
```

и фрагмент конфига виртуального хоста для bot.snovidec.ru (без паролей и доменов, если секретно).
