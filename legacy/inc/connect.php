<?php

if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');


/* Database config */

if($_SERVER['SERVER_NAME']=='localhost'){
    $db_host    = 'localhost';
} else {
    $db_host    = 'kriegerdb.db.10424383.hostedresource.com';
}

$db_user        = 'kriegerdb';
$db_pass        = 'kr13G3R@@';
$db_database    = 'kriegerdb';

/* End config */



$link = mysql_connect($db_host,$db_user,$db_pass) or die('Unable to establish a DB connection');

mysql_select_db($db_database,$link);
mysql_query("SET names UTF8");

?>