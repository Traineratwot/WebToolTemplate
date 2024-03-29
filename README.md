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
2. запустить `composer create-project traineratwot/web-tool-template {project-name}`
3. `cd {project-name}`
4. **!обязательно!** `composer update`
5. Настроить подключение к базе данных в `core/config.php`
6. `composer wt:Install`
7. _необязательно_ `composer wt:composer-config-update` - поможет вашей IDE ориентироваться в константах
8. _необязательно_ `wt DevServer` - запустит наблюдатель который бдует обновлять страницу в браузере при изменении фалов

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
	- `core/components` => Каталог с компонентами

- #### console tool

	- `wt error` - показать журналы ошибок
	- `wt error clear` - очистить журналы ошибок

	- `wt cache` - очистить кеш ошибок

	- `wt makeAjax {name} {type? 'get'|'post'}` - создать класс метода ajax. eg: `wt make ajax "logout"`
	- `wt makeTable {name} {primaryKey? 'id'}` - создать класс объекта таблицы. eg: `wt make table "users"`
	- `wt makePage {url} {template? 'base'}` - создать класс и шаблон страницы для URL. eg: `wt make page "catalog/page1 base"`
	- `wt makeCron {path}` - создать cron. eg: `wt make cron "category/test"`

	- `wt lang {locale}` - создать файл локали .po из проекта исходного кода. eg: `wt lang ru_RU.utf8`
	- `wt lang clear` - очистить кеш языков eg: `wt lang clear`
	- `wt lang all` - показать доступные языки eg: `wt lang all`

	- `wt cron {path to controller}` - сгенерировать команду запуска для crontab eg: `wt cron "category/test.php"`
	- `wt cron {path to controller} run` - попробовать запустить задание cron: `wt cron "category/test.php" run`

  #in develop

	- `components create {name}` - создает новый компонент
	- `components package {name}` - упаковывает компонент в транспортный пакет
	- `components install {name}` - устанавливает компонент из транспортного пакета
	- `components make{Ajax|Table|Page} {name} ...` - аналогично `wt make...` только для компонента

- #### Пользователь

	- login: ~~admin@example.com~~
	- password: ~~admin123~~

- #### Локализация

1. Отредактируйте функцию `WT_LOCALE_SELECT_FUNCTION` в конфиге под свой способ определить язык пользователя
2. Создать файл локали .po из исходного кода проекта eg: `wt lang ru`
3. Отредактируйте файл .po в паке `locale`. Я использую poEdit для этого
4. Готово
5. Если не работает - отключите `gettext` в конфиге

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

#### Config

```php


Config::get('key','?namespace') //return value;
Config::set('key','value','?namespace') //set value;
// тоже самое но с возможностью перезаписывать в процессе выполнения
ConfigOverridable::set('OverridableKey','value','?namespace')
ConfigOverridable::get('OverridableKey','?namespace')
Config::get('OverridableKey','?namespace') //return value;
```

# FAQ

- Композер выдает фатальную ошибку?
	- Это значит что у вас устаревшая версия `composer` обновите его или используйте `php composer.phar ...`