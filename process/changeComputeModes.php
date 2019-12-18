<?php

require_once '../loan/request/request.data.php';
ini_set("display_errors", "On");
error_reporting(E_ALL);
ini_set('max_execution_time', 3000);

//phpinfo();die();

$dt = PdoDataAccess::runquery("select RequestID from LON_requests where StatusID=70 "
		. "and ReqPersonID in(2051) ");

	header("X-Accel-Buffering: no");
	ob_start();
	set_time_limit(0);
	error_reporting(0);

$i=0;
foreach($dt as $row)
{
	echo $i++ . " - " . $row["RequestID"] . " : ";
	
	$obj = LON_ReqParts::GetValidPartObj($row["RequestID"]);
	$obj->ComputeMode = "NEW";
	$obj->EditPart();
	
	$result = LON_installments::ComputeInstallments($row["RequestID"]);
	print_r(ExceptionHandler::PopAllExceptions());
	echo ($result ? "true" : "false") . "<br>";
	ob_flush();
    flush(); 
}

/*PdoDataAccess::runquery("
	update LON_requests
    set LoanID = 9
    where LoanID in(2,3,5,6,13,15,16,51,53)");

PdoDataAccess::runquery("
	update LON_ReqParts join LON_requests using(RequestID)
	set ComputeMode='NOAVARI'
	where ReqPersonID in(1003,2051)");
*/

?>