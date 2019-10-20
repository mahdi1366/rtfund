<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 97.12
//---------------------------

ini_set("display_errors", "On");
ob_start();

require_once '../framework/configurations.inc.php';
set_include_path(DOCUMENT_ROOT . "/generalClasses");

$fp = fopen(DOCUMENT_ROOT . "/storage/loanDaily.html", "w");

require_once '../definitions.inc.php';
require_once DOCUMENT_ROOT . '/generalClasses/InputValidation.class.php';
require_once DOCUMENT_ROOT . '/generalClasses/PDODataAccess.class.php';
require_once DOCUMENT_ROOT . '/generalClasses/DataAudit.class.php';
require_once DOCUMENT_ROOT . '/generalClasses/DateModules.class.php';

define("SYSTEMID", 1);
session_start();
$_SESSION["USER"] = array("PersonID" => 1000);
$_SESSION['LIPAddress'] = '';
$_SESSION["accounting"]["CycleID"] = DateModules::GetYear(DateModules::shNow());

require_once '../office/dms/dms.class.php';
require_once '../loan/request/request.class.php';
//require_once '../commitment/ExecuteEvent.class.php';

echo "--------";
$htmlStr = ob_get_contents();
ob_end_clean(); 
$htmlStr = preg_replace('/\\n/', "<br>", $htmlStr);
fwrite($fp, $htmlStr);
fclose($fp);
die(); 



$query = " select * from LON_requests  r
join LON_ReqParts p on(r.RequestID=p.RequestID AND IsHistory='NO')
where ComputeMode='NEW' AND StatusID=" . LON_REQ_STATUS_CONFIRM;

$reqs = PdoDataAccess::runquery_fetchMode($query);

//........................................................

$objArr = array(
	EVENT_LOANDAILY_innerSource => null, 
	EVENT_LOANDAILY_agentSource_committal => null,
	EVENT_LOANDAILY_agentSource_non_committal => null,
	
	EVENT_LOANDAILY_innerLate => null, 
	EVENT_LOANDAILY_agentlate => null, 
	EVENT_LOANDAILY_innerPenalty => null, 
	EVENT_LOANDAILY_agentPenalty => null
);

$pdo = PdoDataAccess::getPdoObject();
$pdo->beginTransaction();
	
$ComputeDate = DateModules::Now();
echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8"><body dir=rtl>';
echo "<br>****************************<BR>" . DateModules::miladi_to_shamsi($ComputeDate) . 
		"<br>****************************<br>";
while($row = $reqs->fetch())
{
	$eventID = "";
	$LateEvent = "";
	$PenaltyEvent = "";
	
	if($row["ReqPersonID"]*1 == 0)
	{
		$eventID = EVENT_LOANDAILY_innerSource;
		$LateEvent = EVENT_LOANDAILY_innerLate;
		$PenaltyEvent = EVENT_LOANDAILY_innerPenalty;
	}
	else
	{
		if($row["FundGuarantee"] == "YES")
			$eventID = EVENT_LOANDAILY_agentSource_committal;
		else
			$eventID = EVENT_LOANDAILY_agentSource_non_committal;
		
		$LateEvent = EVENT_LOANDAILY_agentlate;
		$PenaltyEvent = EVENT_LOANDAILY_agentPenalty;
	}
	if($objArr[$eventID] != null)
		$obj = &$objArr[$eventID];
	else {
		$objArr[$eventID] = new ExecuteEvent($eventID);
		$obj = &$objArr[$eventID];
	}
	$obj->DocDate = $ComputeDate;
	$obj->Sources = array($row["RequestID"], $row["PartID"] , $ComputeDate);
	$result = $obj->RegisterEventDoc($pdo);
	if(!$result || ExceptionHandler::GetExceptionCount() > 0)
	{
		echo "وام " .  $row["RequestID"] . " : <br>";
		echo ExceptionHandler::GetExceptionsToString("<br>");
		print_r(ExceptionHandler::PopAllExceptions());
		echo "\n--------------------------------------------\n";
	}
	
	
	if($objArr[$LateEvent] != null)
		$obj = &$objArr[$LateEvent];
	else {
		$objArr[$LateEvent] = new ExecuteEvent($LateEvent);
		$obj = &$objArr[$LateEvent];
	}
	$obj->DocDate = $ComputeDate;
	$obj->Sources = array($row["RequestID"], $row["PartID"] , $ComputeDate);
	$result = $obj->RegisterEventDoc($pdo);
	if(!$result || ExceptionHandler::GetExceptionCount() > 0)
	{
		echo "وام " .  $row["RequestID"] . " : <br>";
		echo ExceptionHandler::GetExceptionsToString("<br>");
		print_r(ExceptionHandler::PopAllExceptions());
		echo "\n--------------------------------------------\n";
	}
	
	
	if($objArr[$PenaltyEvent] != null)
		$obj = &$objArr[$PenaltyEvent];
	else {
		$objArr[$PenaltyEvent] = new ExecuteEvent($PenaltyEvent);
		$obj = &$objArr[$PenaltyEvent];
	}
	$obj->DocDate = $ComputeDate;
	$obj->Sources = array($row["RequestID"], $row["PartID"] , $ComputeDate);
	$result = $obj->RegisterEventDoc($pdo);
	if(!$result || ExceptionHandler::GetExceptionCount() > 0)
	{
		echo "وام " .  $row["RequestID"] . " : <br>";
		echo ExceptionHandler::GetExceptionsToString("<br>");
		print_r(ExceptionHandler::PopAllExceptions());
		echo "\n--------------------------------------------\n";
	}

}
$pdo->commit();	

$htmlStr = ob_get_contents();
ob_end_clean(); 
$htmlStr = preg_replace('/\\n/', "<br>", $htmlStr);
fwrite($fp, $htmlStr);
fclose($fp);

die(); 
?>
