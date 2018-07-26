# Mini Php Wrapper
A single file PHP framework.

Usage
--------
```php
<?php
require_once('MiniPhpWrapper.php');

$mpw = new \MPW\MPW();

//$mpw->LK = Links
//$mpw->GL = General Library (Utils)
//$mpw->CL = Custom Library (For your custom utils)
//$mpw->DB = Database class for use MySQL
//$mpw->LG = Site Language library
//$mpw->GA = Google Analytics

//Examples
//Links
echo $mpw->LK->URL_STATIC;
//GET data
$clientIp = $mpw->GL->getClientIP();
$pageId = $mpw->GL->GET_int('pageId');
$pageName = $mpw->GL->GET_str('pageName');
//POST data
$pageId = $mpw->GL->POST_int('pageId');
$pageName = $mpw->GL->POST_str('pageName');
//Google Analytics
//$mpw->GA->sendItem('', '', '', '', '');
//$mpw->GA->sendTransaction();

//Database
//Firstly you should fill DATABASE_ fields in Data.php file for connect to your database without error
$mpw->DB->connect();
echo $mpw->DB->errorMessage;//For log error
//$mpw->DB->query();

$users = User::getAll($mpw->DB);
var_dump($users);

$user = User::getById($mpw->DB, 1);
echo $user->id . '<br>';
echo $user->name . '<br>';
echo $user->email . '<br>';
?>
```

Test SQL
--------
```sql
CREATE TABLE `user` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`date_register` DATETIME NULL DEFAULT NULL,
	`name` VARCHAR(50) NOT NULL DEFAULT '0',
	`surname` VARCHAR(50) NOT NULL DEFAULT '0',
	`email` VARCHAR(50) NOT NULL DEFAULT '0',
	`password` VARCHAR(50) NOT NULL DEFAULT '0',
	`country` VARCHAR(50) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
ENGINE=MyISAM;
```