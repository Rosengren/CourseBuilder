<?php

require_once(realpath(dirname(__FILE__)) . '/include/database.class.php');

// Database connection details
$db_hostname = 'localhost';
$db_name = 'courseBuilder';
$db_username = 'root';
$db_password = '';


defined("ROOT_PATH")
    or define("ROOT_PATH", "/CourseBuilder/public");

defined("INCLUDE_PATH")
    or define("INCLUDE_PATH", realpath(dirname(__FILE__) . '/include'));
     
defined("TEMPLATES_PATH")
    or define("TEMPLATES_PATH", realpath(dirname(__FILE__) . '/templates'));

defined("LIBRARY_PATH")
    or define("LIBRARY_PATH", realpath(dirname(__FILE__) . '/library'));


// Connect to Database
$db = new database($db_hostname, $db_username, $db_password, $db_name);


// TODO_KR basic format for getting database rows:
// $result = $db->getRowsFromTable("courses");
// while ($row = mysqli_fetch_array($result)) {
// 	echo $row["subject"];
// }

?>