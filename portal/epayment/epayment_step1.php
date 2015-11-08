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

$InstallmentID = $_REQUEST["InstallmentID"];
if($InstallmentID == "")
{
	echo "دسترسی نامعتبر است.";
	die();
}

$obj = new LON_installments($InstallmentID);
if(empty($obj->InstallmentID))
{
	echo "دسترسی نامعتبر است.";
	die();
}

if($obj->StatusID == "100")
{
	echo "پرداخت این قسط قبلا انجام گردیده است.";
	die();
}
$partObj = new LON_ReqParts($obj->PartID);
$ForfeitAmount = 0;
if ($obj->InstallmentDate < DateModules::Now()) 
{
	$forfeitDays = DateModules::GDateMinusGDate(DateModules::Now(),$obj->InstallmentDate);
	$ForfeitAmount = $obj->InstallmentAmount*$partObj->ForfeitPercent*$forfeitDays/100;
}
$amount = $obj->InstallmentAmount*1 + $ForfeitAmount;
//$amount = 1000;

$url = "http://rtfund/portal/epayment/epayment_step2.php";

?>
<form method=post name=f1 id=f1 action='https://kica.shaparak.ir/epay/info'>
	<input type=hidden name=merchantId value='<?= $MerchantID ?>'>
	<input type=hidden name=amount value='<?= $amount ?>'>
	<input type=hidden name=paymentId value='<?= $obj->InstallmentID ?>'>
	<input type=hidden name=revertURL value='<?= $url ?>'>
</form>
<script>document.getElementById('f1').submit();</script>