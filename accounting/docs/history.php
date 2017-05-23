<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.12
//---------------------------

if(!empty($_POST["RequestID"]))
{
	$_REQUEST["FlowID"] = 4;
	$_REQUEST["ObjectID"] = $_POST["RequestID"];

	require_once getenv("DOCUMENT_ROOT") . '/office/workflow/history.php';
	die();
}

?>