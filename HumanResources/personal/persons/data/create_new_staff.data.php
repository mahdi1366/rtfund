<?php
//---------------------------
// programmer:	Mahdipour
// create Date: 90.04.12
//---------------------------
require_once '../../../header.inc.php';
require_once '../../staff/class/staff.class.php';
require_once '../class/staff_tax.class.php';
require_once inc_response;
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) {   
		
	case "CreateStaff" :
		  CreateNewStaff();
        
}

function CreateNewStaff()
{		
    $PID = $_POST['personid'];
    $PT =  $_POST['person_type'];
    $STID = $_POST['staff_id'];

    $res = manage_staff::Create_New_Staff($PID,$PT);

    if(!empty($res))
    {
        Response::createObjectiveResponse(true, "{STID:".$res." ,PID:".$PID."}");
		die();
    }
    else {
        Response::createObjectiveResponse(false, ExceptionHandler::ConvertExceptionsToJsObject());
        die();
    }
    		
}



