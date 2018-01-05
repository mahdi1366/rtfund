<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------
require_once("../header.inc.php");

$Image = PdoDataAccess::runquery("select PersonPic from BSC_persons where PersonID=?",array($_GET["PersonID"]));

header('Content-type: image/jpg');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header("Content-Transfer-Encoding: binary");

echo $Image[0][0] ;
die();

?>