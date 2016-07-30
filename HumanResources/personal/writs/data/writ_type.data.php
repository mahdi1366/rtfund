<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------
require_once '../../../header.inc.php';
require_once '../class/writ_type.class.php';
require_once '../class/writ_subtype.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task)
{
	case "WritTypeSave":
		  WritTypeSave();

    case "WritTypeDelete":
          WritTypeDelete();

    case "WritSubTypeSave":
          WritSubTypeSave();

    case "WritSubTypeDelete":
          WritSubTypeDelete();

	/*case "GetTreeNodes":
		GetTreeNodes();*/
		case "SelectWritTypes" :
			  SelectWritTypes();
}

function WritTypeSave()
{
		
	if(empty($_POST["wstid"]))
	{

        $wtid = PdoDataAccess::GetLastID("HRM_writ_subtypes", "writ_subtype_id", " person_type=:PT", array(":PT" => $_POST['pt']));
        $wtid++ ; 
		$query = "insert into HRM_writ_subtypes(person_type,writ_type_id,writ_subtype_id,title,print_title,description,
			emp_state , emp_mode , worktime_type)
                                values('" . $_POST["pt"] . "',1,".$wtid.",'".  $_POST["title"] ."','".$_POST["print_title"]."','',0,0,0)";
		PdoDataAccess::runquery($query);
	
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $_POST['pt']."-".$wtid;
		$daObj->TableName = "HRM_writ_types";
		$daObj->execute();

		echo Response::createObjectiveResponse("true", $_POST['pt']."-".$wtid);
	}
	else
	{
		$query = "update writ_types set title = '" . $_POST["title"] . "'
                            where person_type=" . $_POST['pt'] . " and writ_type_id=" . $_POST['id'] ;
		PdoDataAccess::runquery($query);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $_POST['pt']."-".$_POST['id'];
		$daObj->TableName = "job_category";
		$daObj->execute();

		echo Response::createObjectiveResponse("true", $_POST['pt']."-".$_POST['id']);
	}
	die();
}

function WritTypeDelete()
{
    $arr = explode('-',$_POST["id"]);
   
	PdoDataAccess::runquery("delete from writ_types where person_type=" .$arr[3]." and writ_type_id=".$arr[4] );

	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_delete;
	$daObj->MainObjectID = $_POST["id"];
	$daObj->TableName = "writ_types";
	$daObj->execute();
	echo "true";
	die();
}

function WritSubTypeSave()
{
    $obj = new manage_writ_subType($_POST['person_type'], $_POST['writ_type_id']);
    PdoDataAccess::FillObjectByArray($obj, $_POST);

    $obj->time_limited = ( isset($_POST['time_limited']) ) ? $_POST['time_limited'] : 0 ;
    $obj->req_staff_signature = ( isset($_POST['req_staff_signature']) ) ? $_POST['req_staff_signature'] : 0 ;
    $obj->automatic = ( isset($_POST['automatic']) ) ? $_POST['automatic'] : 0 ;
    $obj->edit_fields = ( isset($_POST['edit_fields']) ) ? $_POST['edit_fields'] : 0 ;
    $obj->force_writ_issue = ( isset($_POST['force_writ_issue']) ) ? $_POST['force_writ_issue'] : 0 ;
    $obj->show_in_summary_doc = ( isset($_POST['show_in_summary_doc']) ) ? $_POST['show_in_summary_doc'] : 0 ;

       
	if(empty($_POST["writ_subtype_id"]))
	{
			$result = $obj->AddWST();
	}
	else
	{
		$obj->writ_subtype_id = $_POST["writ_subtype_id"];
		$result = $obj->EditWST();
	}
      
	echo Response::createObjectiveResponse( ($result == true ) ? "true" : "false" , $obj->person_type."-".$obj->writ_type_id."-".$obj->writ_subtype_id);
	die();
    
}

function WritSubTypeDelete()
{
    manage_writ_subType::DeleteWST($_POST["id"]);
	echo "true";
	die();
   	
}


//............................................................................................................................

function SelectWritTypes()
{
	
	$query = " SELECT  wt.writ_type_id , wt.person_type , wt.title writTitle , wst.title writSubTitle , 'قراردادی' PTitle ,
		wst.writ_subtype_id
				FROM HRM_writ_types wt
							INNER JOIN HRM_writ_subtypes wst 
								ON wt.person_type = wst.person_type and wt.writ_type_id  = wst.writ_type_id
				";
	$temp = PdoDataAccess::runquery($query);
	
	echo dataReader::getJsonData($temp, count($temp), $_GET ["callback"]);
	die();
	
}



?>