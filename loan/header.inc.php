<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';

set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalUI/ext4");

require_once getenv("DOCUMENT_ROOT") . '/definitions.inc.php';

require_once 'PDODataAccess.class.php';
require_once 'classconfig.inc.php';
require_once 'DataAudit.class.php';
require_once 'ExtraModules.class.php';
require_once 'component.class.php';

require_once getenv("DOCUMENT_ROOT") . '/framework/session.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/management/framework.class.php';

session::sec_session_start();
if(!session::checkLogin())
{
	echo "<script>window.location='/framework/login.php';</script>";
	die();
} 

define("SYSTEMID", 6);

//ini_set("display_errors", "On");

$address_prefix = getenv("DOCUMENT_ROOT");
$script = preg_split('/\//', $_SERVER["SCRIPT_NAME"]);
$js_prefix_address = implode("/" , array_splice($script,0,	count($script)-1)) . "/";

if(isset($_REQUEST["framework"]))
{
	$branches = FRW_access::GetAccessBranches();
	if(count($branches) == 0)
	{
		echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8" >';
		echo "<h3><br><br><span style=font-family:tahoma;font-size:15px><center>شما به هیچ شعبه ایی دسترسی ندارید".
				"<br>لطفا با مسئول سیستم تماس بگیرید<br><br>".
				"<a href='/framework/systems.php'>بازگشت</a></center></span></h3>";
		die();
	}
}

define("SHEKOOFAI", 1003);
?>
