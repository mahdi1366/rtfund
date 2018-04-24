<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
//---------------------------
require_once '../../../header.inc.php';
require_once(inc_response);
require_once inc_dataReader;
require_once inc_PDODataAccess;
require_once '../class/devotion.class.php';

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

switch ( $task) 
{
	case "selectDevot":
		  selectDev();
		  
    case "saveDevotion";
	      saveDevData();	

    case "DelDevotion";
          DelDev();  
         
}
function selectDev ()
{ 
	$where = " d.PersonID = :PID ";
	$whereParam = array();
	$whereParam[":PID"] = $_GET["Q0"];
	    
	$no = manage_person_devotion::CountDevotion($where, $whereParam);

	$where .=  dataReader::makeOrder(); 
	$where .= isset($_GET ["start"]) ? " limit " . $_GET ["start"] . "," . $_GET ["limit"] : "";
	
	$temp = manage_person_devotion::GetAllDevotions($where,$whereParam);

	echo dataReader::getJsonData ($temp, $no, $_GET ["callback"] );
	die ();
		
}

function saveDevData(){
	
	$obj = new manage_person_devotion();
	
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	$obj->PersonID = $_POST['PersonID'];
	$obj->from_date = DateModules::Shamsi_to_Miladi($_POST["from_date"]);
	$obj->to_date = DateModules::Shamsi_to_Miladi($_POST["to_date"]);	
	$obj->amount = (empty($obj->amount)) ? "0" : $obj->amount;
        $obj->continous = (!empty($obj->continous)) ? $obj->continous : 0 ; 
	$obj->enlisted = (!empty($obj->enlisted)) ? $obj->enlisted : 0 ;  	
		
	if(empty($_POST["devotion_row"]))
	{		
		$return = $obj->AddDevotion();
	}
	else 
	{ 
		$return = $obj->EditDevotion();
	}	
	
	echo $return ? Response::createObjectiveResponse(true,$obj->devotion_row) :
		Response::createObjectiveResponse(false,ExceptionHandler::GetExceptionsToString("\n"));
	die();
		
}

function DelDev()
{
	$return = manage_person_devotion::RemoveDevotion($_POST['PersonID'],$_POST['devotion_row']);
	if($return !== true)
	{
		echo $return;
		die();
	} 
				
	echo Response::createObjectiveResponse("true", $_POST['PersonID']);
    die();
		
}
?>