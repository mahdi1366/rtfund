<?php
require_once 'config.class.php';
require_once 'PasDB.class.php';
class sys_config{
	 public static $db_server = array (
	          "driver"   => "",
	          "host"     => "",
	          "database" => "",
	          "user"     => "",
	          "pass"     => ""
	 );
	 public static $page_authorize = true;
}

sys_config::$db_server = array (
       "driver" => config::$db_servers['master']["driver"],
       "host" => config::$db_servers['master']["host"],
       "database" => PasDB::DB_NAME,
       "user" => config::$db_servers['master']["pas_user"],
       "pass" => config::$db_servers['master']["pas_pass"]
   );

?>
