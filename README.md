# WebToolTemplate

Простой фреймворк для небольшого веб-приложения

#### includes

- Bootstrap - style
- Jquery - javascript
- Smarty - php templates
- gettext - locale
- LiteSql,mysql,... - database

#### Установка

1. распаковать это в директорию
2. запустить `run install.php`

#### Instructions

- ###### File Structure
	- `locale` => `gettext` переводы
	- `core` => Закрытый от внешнего доступа каталог с ядром системы
	- `core/model` => Папка с основными скриптами. ничего там не трогайте
	- `core/pages` => Каталог cо страницами сайта. имена файлов должны совпадать с URL либо используйте `core/router.php`
	- `core/view` => Каталог с php-кодом, который выполняется перед рендерингом соответствующей страницы. имена файлов должны совпадать с URL либо используйте `core/router.php`
	- `core/templates` => Каталог с `Smarty` шаблонами
	- `core/database` => Каталог с базой данных SQLite, Вы можете использовать любую базу данных
	- `core/ajax` => каталог с файлами php, доступными пользователям. имя файла должно совпадать с именем метода в поле действия в форме. вызов index.php?a=[имя файла без расширения]
	- `core/cron/controllers`=> Папка с крон заданиями. смотри  `wt cron`
	- `core/config.php `=> основной файл конфигурации
	- `core/classes` => Каталог с вашими классами и скриптами
	- `core/classes/smarty/plugins` => Каталог с пользовательскими `Smarty` плагинами
	- `core/classes/tables` => Каталог с классами расширяет BdObject для работы с таблицей БД. смотри  `wt make table`
	- `core/classes/traits` => Каталог с полезными `traits`

- #### console tool

	- `wt error` - показать журналы ошибок
	- `wt error clear` - очистить журналы ошибок

	- `wt cache` - очистить кеш ошибок

	- `wt make ajax {name} {type? 'get'|'post'}` - создать класс метода ajax. eg: `wt make ajax "logout"`
	- `wt make table {name} {primaryKey? 'id'}` - создать класс объекта таблицы. eg: `wt make table "users"`
	- `wt make page {url} {template? 'base'}` - создать класс и шаблон страницы для URL. eg: `wt make page "catalog/page1 base"`
	- `wt make cron {path}` - создать cron. eg: `wt make cron "category/test"`

	- `wt lang {locale}` - создать файл локали .po из проекта исходного кода. eg: `wt lang ru_RU.utf8`
	- `wt lang clear` - очистить кеш языков eg: `wt lang clear`
	- `wt lang all` - показать доступные языки eg: `wt lang all`

	- `wt cron {path to controller}` - сгенерировать команду запуска для crontab eg: `wt cron "category/test.php"`
	- `wt cron {path to controller} run` - попробовать запустить задание cron: `wt cron "category/test.php" run`

- #### Пользователь

	- login: ~~admin@example.com~~
	- password: ~~admin123~~

- #### Локализация

1. Отредактируйте функцию `WT_LOCALE_SELECT_FUNCTION` в конфиге под свой способ определить язык пользователя
2. Создать файл локали .po из исходного кода проекта eg: `wt lang ru`
3. Отредактируйте файл .po в паке `locale`. Я использую poEdit для этого
5. Готово
6. Если не работает - отключите `gettext` в конфиге

## API

#### Cache

```php
/**
 * @param $key mixed
 * @param $value mixed
 * @param $expire int
 * @param $category string
 * @return mixed
 */
Cache::setCache($key,$value,$expire=600,$category = '');

/**
 * @param $key mixed
 * @param $category string
 * @return mixed|null
 */
Cache::getCache($key,$category = '');
/**
 * @param $key mixed
 * @param $category string
 * @return bool
 */
Cache::removeCache($key,$category = '');
```

#### Console

```php
Console::info('text') //print cyan text;
Console::success('text') //print green text;
Console::warning('text') //print yellow text;
Console::failure('text') //print red text;
Console::prompt('Are you sure you?', ?hidden)// ask user in console
/**
 * @param $string
 * @param $foreground_color
 * @param $background_color
 * @return mixed|string
 */
Console::getColoredString('text','red','yellow') //return colored string

Console::foreground_colors //list text color
Console::background_colors //list background color
```