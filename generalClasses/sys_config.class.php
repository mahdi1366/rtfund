<?php
require_once 'config.class.php';
require_once 'ACC_DB.class.php';

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
	       "database" => "framework",
	       "user" => config::$db_servers['master']["framework_user"],
	       "pass" => config::$db_servers['master']["framework_pass"]
	   );

?>
