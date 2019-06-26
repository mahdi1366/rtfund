<?php

require_once '../loan/request/request.data.php';
ini_set("display_errors", "On");

$dt = PdoDataAccess::runquery("select RequestID from LON_requests where StatusID=70 "
		. "and ReqPersonID in(1003,2051)");
flush();
ob_flush();
$i=0;
foreach($dt as $row)
{
	echo $i++ . " - " . $row["RequestID"] . " : ";
	
	$result = ComputeInstallments($row["RequestID"], true, null);
	print_r(ExceptionHandler::PopAllExceptions());
	echo ($result ? "true" : "false") . "<br>";
	flush();
	ob_flush();
}
die();
?>