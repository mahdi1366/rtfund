<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.12
//---------------------------

if(!empty($_POST["RequestID"]))
{
	require_once getenv("DOCUMENT_ROOT") . '/definitions.inc.php';
	$FlowID = constant("FLOWID_TRAFFIC_" . $_REQUEST["ReqType"]);
	
	$_REQUEST["FlowID"] = (int)$FlowID;
	$_REQUEST["ObjectID"] = $_POST["RequestID"];

	require_once getenv("DOCUMENT_ROOT") . '/office/workflow/history.php';
	die();
}

?>