<?php
//-----------------------------
//	developer	: Mahdipour
//	Date		:  98.07
//-----------------------------
require_once '../header.inc.php';
require_once 'definitions.inc.php';
require_once 'ManageGroup.class.php';

switch($_GET["source"])
{
	case "GrpPic":
		if(!empty($_REQUEST["GID"]))
		{
            //.................. secure section .....................
            InputValidation::validate($_REQUEST["GID"], InputValidation::Pattern_Num);         
            //.......................................................
			$obj = new manage_msg_group((int)$_REQUEST['GID']);
         
			$fileName = GRPPIC_DIRECTORY . $obj->GID . "." . $obj->FileType;
			$fileType = $obj->FileType;
                        
		}
		
		break;
}

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
