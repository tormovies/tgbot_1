#!/bin/bash
# Запуск на сервере: bash fix-403.sh (из каталога, где лежит скрипт, или указать путь)
# Исправляет права для доступа Apache к tgbot98

set -e
BASE="${1:-$HOME/bot.snovidec.ru/public_html}"
TG="$BASE/tgbot98"

echo "Путь к сайту: $BASE"
echo "Путь к боту:  $TG"
echo ""

if [ ! -d "$TG" ]; then
  echo "Ошибка: папка $TG не найдена."
  exit 1
fi

# Узнать пользователя Apache (часто apache или www-data)
APACHE_USER=$(ps aux | grep -E '[a]pache|[h]ttpd' | head -1 | awk '{print $1}')
if [ -z "$APACHE_USER" ]; then
  APACHE_USER="www-data"
  echo "Пользователь Apache не определён, используем: $APACHE_USER"
else
  echo "Пользователь Apache: $APACHE_USER"
fi

echo ""
echo "Выставляю права..."

# Каталоги 755, файлы 644 (обязательно)
find "$BASE" -type d -exec chmod 755 {} \;
find "$BASE" -type f -exec chmod 644 {} \;

# Владелец — Apache (нужен sudo; на shared-хостинге часто не нужен — пропусти эту строку)
# sudo chown -R "$APACHE_USER:$APACHE_USER" "$BASE"

# Папка data — запись для скриптов
chmod 755 "$TG/data"

echo "Готово. Проверь в браузере: http://bot.snovidec.ru/tgbot98/"
echo "Если 403 остаётся — смотри HTACCESS_403.md (конфиг Apache, SELinux)."
