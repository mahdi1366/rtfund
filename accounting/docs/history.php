<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	95.08
//---------------------------

if(!empty($_POST["DocID"]))
{
	require_once getenv("DOCUMENT_ROOT") . '/definitions.inc.php';
	$FlowID = FLOWID_ACCDOC;
	
	$_REQUEST["FlowID"] = (int)$FlowID;
	$_REQUEST["ObjectID"] = $_POST["DocID"];

	require_once getenv("DOCUMENT_ROOT") . '/office/workflow/history.php';
	die();
}
die();
?>