<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------
require_once '../../../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once '../class/job_history.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) 
{
	case "SearchHistoryJob":
		  SearchHistoryJob();
		  
    case "SaveJob";
	      SaveJob();	

    case "deleteJH";
          deleteJH();  
         
}
function SearchHistoryJob ()
{ 
	$where = " PersonID = :PID ";
	$whereParam = array();
	$whereParam[":PID"] = $_GET["Q0"];
	
	$no = manage_person_job::CountPersonJob($where, $whereParam);

	$where .=  dataReader::makeOrder(); 
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";
	
	$temp = manage_person_job::GetAllPersonJob($where,$whereParam);

	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
		
}

function SaveJob(){
			
	$obj = new manage_person_job();
	
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$obj->FromDate = DateModules::Shamsi_to_Miladi($obj->FromDate);
	$obj->ToDate = DateModules::Shamsi_to_Miladi($obj->ToDate);
		
		
	if(empty($obj->RowNO))
	{
		$return = $obj->AddJobHistory();
	}
	else 
	{ 
		$return = $obj->EditJobHistory();
	}	
	
	echo $return ? Response::createObjectiveResponse(true,$obj->RowNO) :
		Response::createObjectiveResponse(false,ExceptionHandler::GetExceptionsToString("\n"));
	die();
		
}

function deleteJH()
{
	$return = manage_person_job::RemoveJobHistory($_POST['PID'],$_POST['RowNO']);
	if($return !== true)
	{
		echo $return;
		die();
	} 
				
	echo Response::createObjectiveResponse("true", $_POST['PID']);
    die();
		
}
?>