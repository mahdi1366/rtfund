<?php
require_once 'request.data.php';

$dt = PdoDataAccess::runquery("select PartID from LON_ReqParts  join LON_requests using(RequestID) where StatusID=70 ");
flush();
ob_flush();
$i=0;
foreach($dt as $row)
{
	echo $i++ . " - " . $row["PartID"] . " : ";
	$result = ComputeInstallments($row["PartID"], true);
	echo ($result ? "true" : "false") . "<br>";
	flush();
	ob_flush();
}
die();
?>