<?php

//-----------------------------
//	developer	: Sh.Jafarkhani
//	Date		: 96.07.02
//-----------------------------
require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';
set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");
require_once 'PDODataAccess.class.php';

require_once 'framework.class.php';
require_once '../../definitions.inc.php';

$obj = new FRW_pics($_REQUEST['PicID']);
$fileName = FILE_FRAMEWORK_PICS . "pic#" . $obj->PicID . "." . $obj->FileType;
$fileType = $obj->FileType;

if (file_exists($fileName)) {

    header('Content-disposition: filename=file');  
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header("Content-type: $fileType");
    header("Content-Transfer-Encoding: binary");
    echo file_get_contents($fileName);
} else {    
    echo "محتواي فايل موجود نمي باشد.";
}
die();

?>
