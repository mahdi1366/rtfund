<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.11
//-----------------------------

require_once '../header.inc.php';
require_once 'plan.class.php';

require_once inc_dataGrid;
require_once inc_component;

if(empty($_REQUEST["PlanID"]))
	die();

$PlanID = $_REQUEST["PlanID"];
$PlanObj = new PLN_plans($PlanID);
$ExpertStatusDesc = '';
//-----------------------------------------------------
if(isset($_SESSION["USER"]["framework"]))
	$User = "Staff";
else
{
	if($_SESSION["USER"]["IsCustomer"] == "YES" && $PlanObj->PersonID == $_SESSION["USER"]["PersonID"])
		$User = "Customer";
	else if($_SESSION["USER"]["IsExpert"] == "YES")
		$User = "Expert";
	
	else if($_SESSION["USER"]["IsSupporter"] == "YES")
		$User = "Supporter";
	
	if($User == "Expert")
	{
		$dt = PLN_experts::Get(" AND PersonID=?", array($_SESSION["USER"]["PersonID"]));
		if($dt->rowCount() == 0)
		{
			echo "دسترسی غیر مجاز";
			die();
		}
		$dt = $dt->fetch();
		$ExpertStatusDesc = $dt["StatusDesc"];
	}
}
//-----------------------------------------------------
$readOnly = true;
if($_SESSION["USER"]["IsCustomer"] == "YES" && 
		$PlanObj->PersonID == $_SESSION["USER"]["PersonID"] &&
		($PlanObj->StepID == STEPID_RAW || $PlanObj->StepID == STEPID_RETURN_TO_CUSTOMER))
	$readOnly = false;

if(isset($_SESSION["USER"]["framework"]) && $PlanObj->StepID == STEPID_CUSTOMER_SEND)
	$readOnly = false;

if(isset($_POST["ReadOnly"]) && $_POST["ReadOnly"] == "true")
	$readOnly = true;

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
		<div id="div_plan"></div>
		<form id="mainForm"></form>
	</div>
</center>