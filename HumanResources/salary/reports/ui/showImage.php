<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	89.07.04
//---------------------------
require_once("../../../header.inc.php");
require_once ("../../../personal/persons/class/person.class.php");

	$Image = manage_person::GetPersonPicture($_GET["PersonID"]);
	
	header('Content-disposition: filename=test.jpg');
	header('Content-type: image/jpg');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header("Content-Transfer-Encoding: binary");

	echo $Image ;
	die();

?>