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

$dt = PdoDataAccess::runquery("select FileType,FileContent from DMS_documents 
	where DocumentID=? AND ObjectID=?", array($DocumentID, $ObjectID));

if(count($dt) == 0)
	die();

header('Content-disposition: filename=file.' . $dt[0]["FileType"]);
header('Content-type: jpg');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header("Content-Transfer-Encoding: binary");

echo $dt[0]["FileContent"] . 
	file_get_contents(getenv("DOCUMENT_ROOT") . "/storage/documents/" . $DocumentID . "." . $dt[0]["FileType"]);
die();


?>