<?php

//-----------------------------
//	Programmer	: B.Mahdipour
//	Date		: 94.05
//-----------------------------

require_once "../../header.inc.php";

$DecisionID = $_REQUEST['DecisionID'];
$res = PdoDataAccess::runquery("select FileType from suggest.decisions where DecisionID = ?", array($DecisionID));
$extension = $res[0]['FileType'];

$fname = "/mystorage/DecisionDocuments/SubjectDoc/" . $DecisionID . "." . $extension;

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
