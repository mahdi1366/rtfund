<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.11
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;
require_once 'plan.class.php';

if(empty($_REQUEST["PlanID"]))
	die();

$PlanID = $_REQUEST["PlanID"];
$PlanObj = new PLN_plans($PlanID);
//-----------------------------------------------------
if(isset($_SESSION["USER"]["framework"]))
	$User = "Staff";
else
{
	if($_SESSION["USER"]["IsAgent"] == "YES")
		$User = "Agent";
	else if($_SESSION["USER"]["IsCustomer"] == "YES")
		$User = "Customer";
}
//-----------------------------------------------------
$readOnly = true;
if($_SESSION["USER"]["IsCustomer"] == "YES" && 
		$PlanObj->PersonID == $_SESSION["USER"]["PersonID"] &&
		($PlanObj->StatusID == "1" || $PlanObj->StatusID == "5"))
	$readOnly = false;

if(isset($_SESSION["USER"]["framework"]) && $PlanObj->StatusID == "2")
	$readOnly = false;

//-----------------------------------------------------
require_once 'PlanInfo.js.php';

if(isset($_SESSION["USER"]["framework"]))
	echo "<br>";
?>
<style>
	.desc{
		text-align: justify;
		line-height: 20px;
		margin:0 10 0 10;
	}
	.filled {
		font-weight: bold !important;
	}
	.reject a {
		color : red !important;
	}
	.confirm a {
		color : green !important;
	}
</style>
<center>
	<div align="right" style="width:760px"> 
		<form id="mainForm"></form>
	</div>
</center>