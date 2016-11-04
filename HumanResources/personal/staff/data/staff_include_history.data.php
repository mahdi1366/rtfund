<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.08
//---------------------------


 require_once '../../../header.inc.php';
 require_once '../../staff/class/staff_include_history.class.php'; 
 
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ($task) {
	
	case "selectIncludeHistory" :
		  selectIncludeHistory ();
	    
}

function selectIncludeHistory()
{   
	$personid =$_REQUEST["PID"];
	$temp = manage_staff_include_history::GetAllStaffIncludeHistory($personid);
	
	echo dataReader::getJsonData($temp, count($temp), $_GET ["callback"]);
	die();
}

?>