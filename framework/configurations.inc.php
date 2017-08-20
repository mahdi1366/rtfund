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
	          "user"     => "user1",
	          "pass"     => "user1"
	 );
}

class smtp_config{
	public static $server = "panther.mrservers.net";
	public static $username = "admin@krrtf.ir";
	public static $password = "Heag7j35Y2";
	public static $FromAddress = "admin@krrtf.ir";
}

class sms_config{
	public static $server = "http://79.175.176.61/Service.asmx?wsdl";
	public static $username = "abdolahi";
	public static $password = "09153750964";
	public static $LineNumber = "20001183";
}

define("BANK_AYANDEH_PIN", "qn75G3KAr0R03J5lCm6X");
 
define("SoftwareName", "صندوق پژوهش و فناوری غیر دولتی استان خراسان رضوی");
define("OWNER_NATIONALID", "10380491265");
define("OWNER_REGCODE", "33943");
define("OWNER_REGDATE", "1387/06/24");
define("OWNER_ADDRESS", "شعبه دانشگاه فردوسی مشهد : پردیس، درب غربی( ورودی شهید باهنر ) <br>" . 
	"email : krfn.ir@gmail.com <br> تلفن : 38837392 - فکس : 38837392 ".
	"<br> کد پستی : 9177948974" );


define("SYSTEMID_framework", 1);
define("SYSTEMID_accounting", 2);
define("SYSTEMID_office", 4);
define("SYSTEMID_loan", 6);
define("SYSTEMID_dms", 7);
define("SYSTEMID_plan", 9);
define("SYSTEMID_hrms", 10);
define("SYSTEMID_attendance", 11);
define("SYSTEMID_contract", 12);
define("SYSTEMID_portal", 1000);
?>
