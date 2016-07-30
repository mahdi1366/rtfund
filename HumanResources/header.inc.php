<?php
//-----------------------------
//	Programmer	: B.Mahdipour
//	Date		: 94.11
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';

set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalUI/ext4");

require_once 'PDODataAccess.class.php';
require_once 'classconfig.inc.php';
require_once 'DataAudit.class.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/management/framework.class.php';


require_once getenv("DOCUMENT_ROOT") . '/framework/session.php';
session::sec_session_start();
if(!session::checkLogin())
{
	echo "<script>window.location='/framework/login.php';</script>";
	die();
}

define("SYSTEMID", 10);

$address_prefix = getenv("DOCUMENT_ROOT");

	require_once $address_prefix . "/HumanResources/global/domain.class.php";
	require_once $address_prefix . "/HumanResources/global/exception.class.php";
	require_once $address_prefix. "/HumanResources/global/definitions.inc.php";
	
  	/*
	require_once $address_prefix. "/HumanResources/global/basicInfoDefinitions.inc.php";
  	require_once $address_prefix. "/HumanResources/global/variables.class.php";
	require_once $address_prefix. "/HumanResources/global/access/class/access.class.php";
	require_once $address_prefix. "/HumanResources/global/module_definitions.inc.php";
	require_once $address_prefix . "/HumanResources/global/salary_utils.inc.php";
	require_once $address_prefix . "/HumanResources/global/W2D.class.php"; */ 
	
	define("inc_manage_post", $address_prefix . "/HumanResources/organization/positions/post.class.php");
  	define("inc_manage_unit", $address_prefix . "/HumanResources/organization/org_units/unit.class.php");
	define("inc_manage_staff", $address_prefix . "/HumanResources/personal/staff/class/staff.class.php");

define("HR_ImagePath", "/HumanResources/img/");
define("HR_TemlDirPath", "/HumanResources/tempDir/");
define("inc_QueryHelper", $address_prefix . "/HumanResources/global/report/QueryHelper.php");

$js_prefix_address = implode("/" , 
		array_splice(preg_split('/\//', $_SERVER["SCRIPT_NAME"]),0,
		count(preg_split('/\//', $_SERVER["SCRIPT_NAME"]))-1)) . "/";
?>