<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

class sys_config{
	 public static $db_server = array (
	          "driver"   => "mysql",
	          "host"     => "localhost",
	          "database" => "rtfund",
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

define("Default_Agent_Loan", "9");
?>