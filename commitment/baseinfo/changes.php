<?php
require_once "../../framework/header.inc.php";
?>
<META http-equiv=Content-Type content="text/html; charset=UTF-8" ><body dir="rtl">
<?php
if(isset($_POST["submit"]))
{
	merging($_POST["main"],$_POST["sub"]);
}

function merging($main,$sub){
	
	$params = array(
		":m" => $main,
		":s" => $sub
	);
	PdoDataAccess::runquery("update ACC_CostCodes set level2=:m where level2=:s", $params);
	echo "update ACC_CostCodes - level2 : " . PdoDataAccess::AffectedRows() . "<br>";
	
	PdoDataAccess::runquery("update ACC_CostCodes set level3=:m where level3=:s", $params);
	echo "update ACC_CostCodes - level3 : " . PdoDataAccess::AffectedRows() . "<br>";
	
	PdoDataAccess::runquery("delete from  ACC_blocks where blockID=?", array($sub));
	echo "delete ACC_blocks : " . PdoDataAccess::AffectedRows() . "<br>";
	
}
?>

<form method="post">
 کد جز حساب اصلی : 
	<input type="text" name="main">
	<br>
	کد جز حسابی که باید در اصلی ادغام شود :
	<input type="text" name="sub">
	<br>
	<input type="submit" name="submit">
</form>
</body>	
