<?php
require_once 'request.data.php';

$dt = PdoDataAccess::runquery("select RequestID LON_requests where StatusID=70 ");
flush();
ob_flush();
$i=0;
foreach($dt as $row)
{
	echo $i++ . " - " . $row["RequestID"] . " : ";
	$result = ComputeInstallments($row["RequestID"], true);
	echo ($result ? "true" : "false") . "<br>";
	flush();
	ob_flush();
}
die();
?>