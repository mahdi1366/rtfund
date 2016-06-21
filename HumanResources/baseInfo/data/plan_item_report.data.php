<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.03
//---------------------------
require_once '../../header.inc.php';
require_once '../class/plan_item_report.class.php';
require_once(inc_response);
require_once inc_dataReader;

$task = isset ( $_POST ["task"] ) ? $_POST ["task"] : (isset ( $_GET ["task"] ) ? $_GET ["task"] : "");

	switch ( $task) {            
            
		case "searchPITR" :
                      searchPITR();
                    
                case "SavePIR" :
                      SavePIR() ; 
                    
                case "removePIR" :
                    removePIR() ; 
                    
                    
                    }

function searchPITR()
{      
        $where = dataReader::makeOrder();
        
        $temp = manage_plan_item_report::GetAll($where);
        $no = count($temp);

        $temp = array_slice($temp, $_GET["start"], $_GET["limit"]);

        echo dataReader::getJsonData ( $temp, $no, $_GET ["callback"] );
        die ();		
}

function SavePIR()
{
	$obj = new manage_plan_item_report();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);	
	
	     	
	if ($obj->PlanItemID == "" )
	{
                $return = $obj->Add();
	}
	else
	{
		$return = $obj->Edit();
	}
	
        if($return)
		echo Response::createResponse(true,$obj->PlanItemID);
	else
		echo Response::createResponse(false , ExceptionHandler::ConvertExceptionsToJsObject());
	die();
            
}

function removePIR()
{
	$return = manage_plan_item_report::Remove($_POST["pid"]);
	Response::createObjectiveResponse($return, (!$return ? ExceptionHandler::popExceptionDescription() : ""));
	die();
}

?>