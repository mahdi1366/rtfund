<?php

define("DB_DBNAME" , "89.165.117.130:1521/mot");
define("DB_USER" , "mot");
define("DB_PASS" , "talfigh");

echo phpversion();
try{
	new PDO("oci:dbname=" . DB_DBNAME . ";charset=AL32UTF8", DB_USER, DB_PASS);
}
catch (PDOException $e) 
{
	echo(' خطا در اتصال به بانک اطلاعاتی');
	echo $e->getMessage();
	die();			
}


?>
