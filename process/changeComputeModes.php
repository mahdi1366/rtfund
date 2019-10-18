<?php

require_once '../loan/request/request.data.php';
ini_set("display_errors", "On");

$dt = PdoDataAccess::runquery("select RequestID from LON_requests where StatusID=70 "
		. "and ReqPersonID not in(1003,2051)");
flush();
ob_flush();
$i=0;
foreach($dt as $row)
{
	echo $i++ . " - " . $row["RequestID"] . " : ";
	
	$obj = LON_ReqParts::GetValidPartObj($row["RequestID"]);
	$obj->ComputeMode = "NEW";
	$obj->EditPart();
	
	$result = ComputeInstallments($row["RequestID"], true, null);
	print_r(ExceptionHandler::PopAllExceptions());
	echo ($result ? "true" : "false") . "<br>";
	flush();
	ob_flush();
}

PdoDataAccess::runquery("
	update LON_requests
	set LoanID = case LoanID when 1 then 1301010000
							 when 54 then 1302010000
							 when 4 then 1303010000
							 when 8 then 1304010000
							 when 11 then 1305010000
							 when 12 then 1306010000
							 when 14 then 1307010000
							 when 55 then 1308010000
							 when 56 then 1309010000
							 when 79 then 1309500007
							 when 80 then 1309500008
							 when 81 then 1309500009
							 when 82 then 1309500010
							 when 83 then 1309500011
							 when 84 then 1309500012
							 else 1309500000 end
	where TafsiliID is null");

PdoDataAccess::runquery("
	update LON_ReqParts join LON_requests using(RequestID)
	set ComputeMode='NOAVARI'
	where ReqPersonID in(1003,2051)");


die();
?>