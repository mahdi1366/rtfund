<?php
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------
require_once 'header.inc.php';

if(empty($_REQUEST["DocumentID"]) || empty($_REQUEST["ObjectID"]))
	die();

$DocumentID = $_REQUEST["DocumentID"];
$ObjectID = $_REQUEST["ObjectID"];

$query = "select RowID,PageNo,FileType,FileContent 
	from DMS_DocFiles df join DMS_documents using(DocumentID)
	where df.DocumentID=? AND ObjectID=?" . (!empty($_REQUEST["RowID"]) ? " AND RowID=?" : "")."
	order by PageNo";
$params = array($DocumentID, $ObjectID);
if(!empty($_REQUEST["RowID"]))
	$params[] = $_REQUEST["RowID"];

$dt = PdoDataAccess::runquery($query, $params);

if(count($dt) == 0)
	die();

if(count($dt) == 1)
{
	header('Content-disposition: filename=file.' . $dt[0]["FileType"]);
	header('Content-type: jpg');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header("Content-Transfer-Encoding: binary");

	echo $dt[0]["FileContent"] . 
		file_get_contents(getenv("DOCUMENT_ROOT") . "/storage/documents/" . $dt[0]["RowID"] . "." . $dt[0]["FileType"]);
	die();
}

function data_uri($content, $mime) {
    $base64 = base64_encode($content);
    return ('data:' . $mime . ';base64,' . $base64);
}

echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>';
echo "<center>";
foreach($dt as $file)
{
	echo "<div style='width:100%;' align=center><hr>صفحه " . $file["PageNo"] . "<hr></div>";
	echo "<img src=";
	echo data_uri($file["FileContent"] . 
		file_get_contents(getenv("DOCUMENT_ROOT") . "/storage/documents/" .
		$file["RowID"] . "." . $file["FileType"]), 'image/jpeg');
	echo " /></br>";
}
echo "</center>";

?>