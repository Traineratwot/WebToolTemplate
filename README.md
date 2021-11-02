# WebToolTemplate

Simple FrameWork for tiny web application

#### includes

- Bootstarp - style
- Jquery - javascript
- Smarty - php templates
- LiteSql,mysql,... - database

#### install

1. unpack this in dirrectoy

2. `run install.php`

#### Instructions

- ###### File Structure
	- core => Closed from external access directory with the core of the system
	- model => Dirretoria with basic scripts. do not touch anything there
	- classes => Dirretoria with your classes and scripts
	- pages => Directory c site pages should. filenames must match url
	- view => Dirretoria with php code that is executed before by rendering the corresponding page. filenames must match url
	- templates => Dirretoria with `Smarty` templates
	- database => Dirretoria with SQLite database, You can use any database
	- ajax => directory with php files available to users. the filename must be the same as the method name in the action field on your form. calling /index.php?a=[filename without extension]
	- config.php => main configuration file

#### console tool

- `wt error` - display error logs
- `wt make ajax {name} {type? 'get'|'post'}` - create ajax method class. eg: `wt make ajax "logout"`
- `wt make table {name} {primaryKey? 'id'}` - create table object class. eg: `wt make table "users"`
- `wt make page {url} {template? 'base'}` - create table object class. eg: `wt make page "catalog/page1 base"`

#### user

- login: admin@example.com
- password: admin123
