<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------
require_once("../header.inc.php");

$object = isset($_REQUEST["PersonSign"]) ? "PersonSign" : "PersonPic";

$Image = PdoDataAccess::runquery("select $object from BSC_persons where PersonID=?",array($_GET["PersonID"]));

if($Image[0][0] == "")
	die();

header('Content-type: image/jpg');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header("Content-Transfer-Encoding: binary");

echo $Image[0][0] ;
die();

?>