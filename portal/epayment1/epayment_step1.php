<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	93.06
//-------------------------
require_once '../header.inc.php';
require_once '../../loan/request/request.class.php';

?>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
<?

$PartID = $_REQUEST["PartID"];
if($PartID == "")
{
	echo "دسترسی نامعتبر است.";
	die();
}

$amount = $_REQUEST["amount"];
$MerchantID = 301328;
$url = "http://rtfund/portal/epayment/epayment_step2.php";

if($_SESSION["USER"]["UserName"] != "9155338872")
	die();
?>
<form method=post name=f1 id=f1 action='https://kica.shaparak.ir/epay/info'>
	<input type=hidden name=merchantId value='<?= $MerchantID ?>'>
	<input type=hidden name=amount value='<?= $amount ?>'>
	<input type=hidden name=paymentId value='<?= $PartID ?>'>
	<input type=hidden name=revertURL value='<?= $url ?>'>
</form>
<script>document.getElementById('f1').submit();</script>