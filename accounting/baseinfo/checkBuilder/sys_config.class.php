<?php
require_once 'config.class.php';

class sys_config{
	 public static $db_server = array (
	          "driver"   => "",
	          "host"     => "",
	          "database" => "",
	          "user"     => "",
	          "pass"     => ""
	 );
	 public static $page_authorize = false;
}
sys_config::$db_server = array (
	       "driver" => config::$db_servers['master']["driver"],
	       "host" => config::$db_servers['master']["host"],
	       "database" => DB_NAME,
	       "user" => config::$db_servers['master']["accountancy_user"],
	       "pass" => config::$db_servers['master']["accountancy_pass"]
	   );

?>
