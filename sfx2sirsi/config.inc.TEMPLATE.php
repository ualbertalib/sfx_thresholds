<?php 

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 

error_reporting(E_ALL);
ini_set('display_errors',1);

date_default_timezone_set('America/Denver');

define("ADMIN_EMAIL",'jhennig@ualberta.ca');

define("EMAIL_ERROR_SUBJECT",'Error in sfx2sirsi');



define('PORT', 3306);
define('DBHOST', 'localhost');
define('DBNAME', 'sfx2sirsi');
define('DBUSER', '');
define('DBPASS', ''); 
 

$myPath = dirname(__FILE__);



//require_once('Database.class.php');
require($myPath . '/FileWriter.class.php');
include ($myPath . '/Threshold2.class.php');
include($myPath . '/helper.inc.php');

$conn = new \PDO("mysql:host=" . DBHOST . ";port=" . PORT .";dbname=" . DBNAME, DBUSER, DBPASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));  
$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  			
// $conn = $connRes->getDBConnect();

//done so errors don't come from a threshold where undef is in the string
define('undef','undef');



?>
