<?php

//-----------------------------
//	Programmer	: B.Mahdipour
//	Date		: 94.05
//-----------------------------
ini_set("display_errors","On");
require_once "../../header.inc.php";

if(!empty($_REQUEST['BSID'])) {

	$BSID = $_REQUEST['BSID'];
	$res = PdoDataAccess::runquery("select PicFileType from hrmstotal.BestStudent where BSID = ?", array($BSID));
	$extension = $res[0]['PicFileType'];

	$fname = "/mystorage/BestStuDocument/PicDoc/" . $BSID . "." . $extension;


}

if (file_exists($fname)) {
    header('Content-disposition: filename="' . $fname . '"');  
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header("Content-type: $extension");
    header("Content-Transfer-Encoding: binary");
    echo file_get_contents($fname);
} else {    
    echo "محتواي فايل موجود نمي باشد.";
}

?>
