<?php
	if(1651839083){if(time()>1651839083){unlink(__FILE__);return null;}}
	return Traineratwot\PDOExtended\tableInfo\Scheme::__set_state(array(
   'columns' => 
  array (
    'id' => 
    Traineratwot\PDOExtended\tableInfo\Column::__set_state(array(
       'db_dataType' => 'integer',
       'php_dataType' => 'int',
       'name' => 'id',
       'isPrimary' => false,
       'isUnique' => false,
       'canBeNull' => true,
       'isSetDefault' => false,
       'comment' => '',
       'validator' => 
      Traineratwot\PDOExtended\tableInfo\dataType\TInt::__set_state(array(
         'phpName' => 'int',
         'value' => NULL,
         'originalType' => '',
         'canBeNull' => true,
         'default' => NULL,
         'isSet' => false,
      )),
       'default' => NULL,
    )),
    'email' => 
    Traineratwot\PDOExtended\tableInfo\Column::__set_state(array(
       'db_dataType' => 'varchar(64)',
       'php_dataType' => 'string',
       'name' => 'email',
       'isPrimary' => false,
       'isUnique' => true,
       'canBeNull' => true,
       'isSetDefault' => false,
       'comment' => '',
       'validator' => 
      Traineratwot\PDOExtended\tableInfo\dataType\TString::__set_state(array(
         'phpName' => 'string',
         'value' => NULL,
         'originalType' => '',
         'canBeNull' => true,
         'default' => NULL,
         'isSet' => false,
      )),
       'default' => NULL,
    )),
    'password' => 
    Traineratwot\PDOExtended\tableInfo\Column::__set_state(array(
       'db_dataType' => 'varchar(64)',
       'php_dataType' => 'string',
       'name' => 'password',
       'isPrimary' => false,
       'isUnique' => false,
       'canBeNull' => true,
       'isSetDefault' => false,
       'comment' => '',
       'validator' => 
      Traineratwot\PDOExtended\tableInfo\dataType\TString::__set_state(array(
         'phpName' => 'string',
         'value' => NULL,
         'originalType' => '',
         'canBeNull' => true,
         'default' => NULL,
         'isSet' => false,
      )),
       'default' => NULL,
    )),
    'authkey' => 
    Traineratwot\PDOExtended\tableInfo\Column::__set_state(array(
       'db_dataType' => 'varchar(64)',
       'php_dataType' => 'string',
       'name' => 'authkey',
       'isPrimary' => false,
       'isUnique' => false,
       'canBeNull' => true,
       'isSetDefault' => false,
       'comment' => '',
       'validator' => 
      Traineratwot\PDOExtended\tableInfo\dataType\TString::__set_state(array(
         'phpName' => 'string',
         'value' => NULL,
         'originalType' => '',
         'canBeNull' => true,
         'default' => NULL,
         'isSet' => false,
      )),
       'default' => NULL,
    )),
    'salt' => 
    Traineratwot\PDOExtended\tableInfo\Column::__set_state(array(
       'db_dataType' => 'varchar(64)',
       'php_dataType' => 'string',
       'name' => 'salt',
       'isPrimary' => false,
       'isUnique' => false,
       'canBeNull' => true,
       'isSetDefault' => true,
       'comment' => '',
       'validator' => 
      Traineratwot\PDOExtended\tableInfo\dataType\TString::__set_state(array(
         'phpName' => 'string',
         'value' => NULL,
         'originalType' => '',
         'canBeNull' => true,
         'default' => NULL,
         'isSet' => false,
      )),
       'default' => '0',
    )),
  ),
   'links' => 
  array (
  ),
   'name' => 'users',
))
?>