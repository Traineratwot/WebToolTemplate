# WebToolTemplate

Simple FrameWork for small web application

#### includes

- Bootstrap - style
- Jquery - javascript
- Smarty - php templates
- gettext - locale
- LiteSql,mysql,... - database

#### install

1. unpack this in Directory

2. `run install.php`

#### Instructions

- ###### File Structure
	- locale => `gettext` translations
	- core => Closed from external access directory with the core of the system
	- core/model => Directory with basic scripts. do not touch anything there
	- core/classes => Directory with your classes and scripts
	- core/pages => Directory c site pages should. filenames must match url
	- core/view => Directory with php code that is executed before by rendering the corresponding page. filenames must match url
	- core/templates => Directory with `Smarty` templates
	- core/database => Directory with SQLite database, You can use any database
	- core/ajax => directory with php files available to users. the filename must be the same as the method name in the action field on your form. calling /index.php?a=[filename without extension]
	- config.php => main configuration file

#### console tool

- `wt error` - display error logs
- `wt error clear` - clear error logs
- `wt cache clear` - clear error cache
- `wt cache clear sudo` - clear error cache with sudo
- `wt make ajax {name} {type? 'get'|'post'}` - create ajax method class. eg: `wt make ajax "logout"`
- `wt make table {name} {primaryKey? 'id'}` - create table object class. eg: `wt make table "users"`
- `wt make page {url} {template? 'base'}` - create table object class. eg: `wt make page "catalog/page1 base"`
- `wt lang {locale}` - generate locale file .po from source code project eg: `wt lang ru_RU.utf8`
- `wt lang clear` - clear lang cache eg: `wt lang clear`
- `wt lang all` - display available languages eg: `wt lang all`

#### user

- login: admin@example.com
- password: admin123

#### localization

1. edit `WT_LOCALE_SELECT_FUNCTION` in config
2. generate locale file .po from source code project
3. edit .po file. I use poEdit for that
4. clear lang cache
5. profit
6. if doesn't work disable `gettext` in config

## API

#### cache

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