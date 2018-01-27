<?php
require_once '../header.inc.php';
require_once inc_reportGenerator;
require_once '../request/request.class.php';
require_once '../request/request.data.php';

$dt = PdoDataAccess::runquery("select * from LON_requests join LON_ReqParts using(RequestID)
	where IsEnded='NO' AND IsHistory='NO'
	group by RequestID ");
$returnArr = array();
foreach($dt as $row)
{
	$RequestID = $row["RequestID"];
	
	$ComputeArr = LON_requests::ComputePayments($RequestID, $dt);
	$PureArr = LON_requests::ComputePures($RequestID);
	//............ get remain untill now ......................
	$CurrentRemain = LON_requests::GetCurrentRemainAmount($RequestID, $ComputeArr);
	$TotalRemain = LON_requests::GetTotalRemainAmount($RequestID, $ComputeArr);
	$DefrayAmount = LON_requests::GetDefrayAmount($RequestID, $ComputeArr, $PureArr);
	
	$returnArr[] = array(
		"RequestID" => $RequestID,
		"PartAmount" => $row["PartAmount"],
		"CurrentRemain" => $CurrentRemain,
		"TotalRemain" => $TotalRemain,
		"DefrayAmount" => $DefrayAmount
	);
}

$rpg = new ReportGenerator();
$rpg->mysql_resource = $returnArr;

function MoneyRender($row,$value){
	if($value*1 < 0)
		return "<font color=red>" . number_format($value) . "</font>";
	return number_format($value);
}

ReportGenerator::BeginReport();
$rpg->addColumn("شماره وام", "RequestID");
$rpg->addColumn("مبلغ وام", "PartAmount","ReportMoneyRender");
$rpg->addColumn("مانده قابل پرداخت معوقه", "CurrentRemain", "MoneyRender");
$rpg->addColumn("مانده تا انتها", "TotalRemain", "MoneyRender");
$rpg->addColumn("مبلغ قابل پرداخت در صورت تسویه وام ", "DefrayAmount", "MoneyRender");

echo $rpg->generateReport();


?>
