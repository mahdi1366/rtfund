<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

class sys_config{
	 public static $db_server = array (
	          "driver"   => "mysql",
	          "host"     => "localhost",
	          "database" => "krrtfir_rtfund",
	          "user"     => "root",
	          "pass"     => "1297"
	 );
}

class smtp_config{
	public static $server = "panther.mrservers.net";
	public static $username = "admin@krrtf.ir";
	public static $password = "Heag7j35Y2";
	public static $FromAddress = "admin@krrtf.ir";
}

define("SoftwareName", "صندوق پژوهش و فناوری خراسان رضوی");
define("OWNER_NATIONALID", "10380491265");
define("OWNER_REGCODE", "33943");
define("OWNER_REGDATE", "1387/06/24");

define("Default_Agent_Loan", "9");
?>