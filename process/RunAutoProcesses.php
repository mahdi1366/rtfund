<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 97.12
//---------------------------

ob_start();

require_once '../framework/configurations.inc.php';

$fp = fopen(DOCUMENT_ROOT . "/storage/loanDaily.html", "w");
set_include_path(DOCUMENT_ROOT . "/generalClasses");

require_once '../definitions.inc.php';
require_once DOCUMENT_ROOT . '/generalClasses/InputValidation.class.php';
require_once DOCUMENT_ROOT . '/generalClasses/PDODataAccess.class.php';
require_once DOCUMENT_ROOT . '/generalClasses/DataAudit.class.php';

require_once '../office/dms/dms.class.php';

require_once '../loan/request/request.class.php';
require_once '../commitment/ExecuteEvent.class.php';

if(empty($_POST["ComputeDate"]))
{
	define("SYSTEMID", 1);
	session_start();
	$_SESSION["USER"] = array("PersonID" => 1000);
	$_SESSION['LIPAddress'] = '';
	$_SESSION["accounting"]["CycleID"] = DateModules::GetYear(DateModules::shNow());
}

$where = "";
$params = array();
$query = "
	select * 
	from LON_requests  r
	join LON_ReqParts p on(r.RequestID=p.RequestID AND IsHistory='NO')
	where ComputeMode='NEW' AND StatusID=" . LON_REQ_STATUS_CONFIRM;
if(!empty($_POST["RequestID"]))
{
	$query .= " AND  r.RequestID=:r";
	$params[":r"] = (int)$_POST["RequestID"];
}
$reqs = PdoDataAccess::runquery($query, $params);

$objArr = array(
	EVENT_LOANDAILY_innerSource => null, 
	EVENT_LOANDAILY_agentSource_committal => null,
	EVENT_LOANDAILY_agentSource_non_committal => null
);

$pdo = PdoDataAccess::getPdoObject();
$pdo->beginTransaction();
	
$ComputeDate = !empty($_POST["ComputeDate"]) ? 
		DateModules::shamsi_to_miladi($_POST["ComputeDate"],"-") : DateModules::Now();
echo '<META http-equiv=Content-Type content="text/html; charset=UTF-8"><body dir=rtl>';
echo "<br>****************************<BR>" . DateModules::miladi_to_shamsi($ComputeDate) . 
		"<br>****************************<br>";
foreach($reqs as $row)
{
	$eventID = "";
	if($row["ReqPersonID"]*1 == 0)
		$eventID = EVENT_LOANDAILY_innerSource;
	else
	{
		if($row["FundGuarantee"] == "YES")
			$eventID = EVENT_LOANDAILY_agentSource_committal;
		else
			$eventID = EVENT_LOANDAILY_agentSource_non_committal;
	}
	
	if($objArr[$eventID] != null)
		$obj = &$objArr[$eventID];
	else {
		$objArr[$eventID] = new ExecuteEvent($eventID);
		$obj = &$objArr[$eventID];
	}
	
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

?>
