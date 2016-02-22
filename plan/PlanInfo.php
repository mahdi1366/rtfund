<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.11
//-----------------------------

require_once 'header.inc.php';
require_once inc_dataGrid;

//$PlanID = !empty($_POST["PlanID"]) ? $_POST["PlanID"] : 0;
$PlanID = 1;

if(isset($_SESSION["USER"]["framework"]))
	$User = "Staff";
else
{
	if($_SESSION["USER"]["IsAgent"] == "YES")
		$User = "Agent";
	else if($_SESSION["USER"]["IsCustomer"] == "YES")
		$User = "Customer";
}
//------------------------------------------------------------------------------

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
</style>
<center>
	<div align="right" style="width:780px"> 
		<form id="mainForm"></form>
	</div>
</center>